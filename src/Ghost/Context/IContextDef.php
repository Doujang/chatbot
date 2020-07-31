<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Ghost\Context;

use Commune\Blueprint\Ghost\Cloner;
use Commune\Blueprint\Ghost\Context;
use Commune\Blueprint\Ghost\Dialog;
use Commune\Blueprint\Ghost\MindDef\AliasesForAuth;
use Commune\Blueprint\Ghost\MindDef\AliasesForContext;
use Commune\Blueprint\Ghost\MindDef\MemoryDef;
use Commune\Blueprint\Ghost\MindDef\StageDef;
use Commune\Blueprint\Ghost\MindMeta\IntentMeta;
use Commune\Blueprint\Ghost\Operate\Operator;
use Commune\Blueprint\Ghost\Ucl;
use Commune\Blueprint\Ghost\MindMeta\MemoryMeta;
use Commune\Blueprint\Ghost\MindMeta\StageMeta;
use Commune\Ghost\IMindDef\IMemoryDef;
use Commune\Ghost\Stage\InitStage;
use Commune\Ghost\Support\ContextUtils;
use Commune\Support\Alias\AbsAliases;
use Commune\Support\Option\AbsOption;
use Commune\Support\Option\Meta;
use Commune\Support\Option\Wrapper;
use Commune\Blueprint\Ghost\MindMeta\ContextMeta;
use Commune\Blueprint\Ghost\MindDef\ContextDef;
use Commune\Blueprint\Exceptions\Logic\InvalidArgumentException;
use Commune\Support\Utils\ArrayUtils;

/**
 * @author thirdgerb <thirdgerb@gmail.com>
 *
 * @property-read string $name      当前配置的 ID
 * @property-read string $title     标题
 * @property-read string $desc      简介
 *
 *
 * ## context wrapper
 * @property-read string $contextWrapper       Context 的包装器.
 *
 *
 * ## 基础属性
 * @property-read int $priority                     语境的默认优先级
 * @property-read IntentMeta $asIntent
 *
 * @property-read string[] $auth                    用户权限
 * @property-read string[] $queryNames              context 请求参数键名的定义, 如果是列表则要加上 []
 *
 *
 * @property-read string[] $memoryScopes
 *
 * @property-read array $memoryAttrs
 * @property-read array $dependingNames
 *
 * @property-read null|array $comprehendPipes
 *
 * @property-read null|string $onCancel
 * @property-read null|string $onQuit
 *
 * @property-read null|string $ifRedirect
 *
 * @property-read string|null $firstStage
 * @property-read string[] $stageRoutes
 * @property-read string[] $contextRoutes
 *
 * @property-read StageMeta[] $stages
 *
 */
class IContextDef extends AbsOption implements ContextDef
{

    /**
     * @var MemoryDef
     */
    protected $_asMemoryDef;

    /**
     * @var StageDef
     */
    protected $_asStageDef;

    /**
     * @var StageMeta[]
     */
    protected $_stageMetaMap;

    public static function stub(): array
    {
        return [
            // context 的全名. 同时也是意图名称.
            'name' => '',
            // context 的标题. 可以用于 精确意图校验.
            'title' => '',
            // context 的简介. 通常用于 askChoose 的选项.
            'desc' => '',
            // context 的优先级. 若干个语境在 blocking 状态中, 根据优先级决定谁先恢复.
            'priority' => 0,

            // auth, 访问时用户必须拥有的权限. 用类名表示.
            'auth' => [],

            // context 的默认参数名, 类似 url 的 query 参数.
            // query 参数值默认是字符串.
            // query 参数如果是数组, 则定义参数名时应该用 [] 做后缀, 例如 ['key1', 'key2', 'key3[]']
            'queryNames' => [],


            // context 实例的封装类.
            'contextWrapper' => '',

            // context 作为意图的默认配置. 可以被覆盖.
            'asIntent' => IntentMeta::stub(),

            // 定义 context 上下文记忆的作用域.
            // 相关作用域参数, 会自动添加到 query 参数中.
            // 作用域为空, 则是一个 session 级别的短程记忆.
            // 不为空, 则是长程记忆, 会持久化保存.
            'memoryScopes' => null,

            // memory 记忆体的默认值.
            'memoryAttrs' => [],

            // Context 启动时, 会依次检查的参数. 当这些参数都不是 null 时, 认为 Context::isPrepared
            'dependingNames' => [],

            'comprehendPipes' => null,

            'onCancel' => null,
            'onQuit' => null,
            'stageRoutes' => [],
            'contextRoutes' => [],
            'firstStage' => null,

            // 预定义的 stage 的配置. StageMeta
            'stages' => [],

            'ifRedirect' => null,
        ];
    }

    public static function relations(): array
    {
        return [
            'stages[]' => StageMeta::class,
            'asIntent' => IntentMeta::class,
        ];
    }

    public function fill(array $data): void
    {
        $asIntent = $data['asIntent'] ?? [];

        if (is_array($asIntent)) {
            $asIntent = IntentMeta::mergeStageInfo(
                $asIntent,
                $data['name'] ?? '',
                $data['title'] ?? '',
                $data['desc'] ?? ''
            );
            $data['asIntent'] = $asIntent;
        }

        $stages = $data['stages'] ?? [];
        foreach ($stages as $shortName => $stage) {
            $stages[$shortName] = StageMeta::mergeContextInfo(
                ArrayUtils::wrap($stage),
                $data['name'] ?? '',
                $stage['stageName'] ?? strval($shortName)
            );
        }

        $data['stages'] = $stages;

        parent::fill($data);
    }

    public static function validate(array $data): ? string /* errorMsg */
    {
        $name = $data['name'] ?? '';

        if (!ContextUtils::isValidContextName($name)) {
            return "contextName $name is invalid";
        }

        return parent::validate($data);
    }

    public function __get_contextWrapper() : string
    {
        $wrapper = $this->_data['contextWrapper'] ?? '';
        $wrapper = empty($wrapper)
            ? IContext::class
            : $wrapper;

        return AliasesForContext::getOriginFromAlias($wrapper);
    }

    public function __set_contextWrapper($name, $value) : void
    {
        $this->_data[$name] = AliasesForContext::getAliasOfOrigin($value);
    }

    public function __set_auth($name, $value) : void
    {
        $this->_data[$name] = array_map(
            [AliasesForAuth::class, AliasesForAuth::FUNC_GET_ALIAS],
            $value
        );

    }

    public function __get_auth() : array
    {
        $auth = $this->_data['auth'] ?? [];
        return array_map(
            [AliasesForAuth::class, AliasesForAuth::FUNC_GET_ORIGIN],
            $auth
        );
    }

    /*------ wrap -------*/

    /**
     * @return ContextMeta
     */
    public function toMeta(): Meta
    {
        $config = $this->toArray();
        unset($config['name']);
        unset($config['title']);
        unset($config['desc']);

        return new ContextMeta([
            'name' => $this->name,
            'title' => $this->title,
            'desc' => $this->desc,
            'wrapper' => static::class,
            'config' => $config
        ]);
    }

    /**
     * @param Meta $meta
     * @return Wrapper
     */
    public static function wrapMeta(Meta $meta): Wrapper
    {
        if (!$meta instanceof ContextMeta) {
            throw new InvalidArgumentException(
                __METHOD__
                . ' only accept meta of subclass ' . ContextMeta::class
            );
        }

        $config = $meta->config;
        $config['name'] = $meta->name;
        $config['title'] = $meta->title;
        $config['desc'] = $meta->desc;
        return new static($config);
    }

    /*------ properties -------*/

    public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->desc;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function onCancelStage(): ? string
    {
        return $this->onCancel;
    }

    public function auth(): array
    {
        return $this->auth;
    }


    public function onQuitStage(): ? string
    {
        return $this->onQuit;
    }

    public function commonStageRoutes(): array
    {
        return $this->stageRoutes;
    }

    public function commonContextRoutes(): array
    {
        return $this->contextRoutes;
    }

    public function getScopes(): array
    {
        return $this->memoryScopes;
    }

    public function getDependingNames(): array
    {
        return $this->dependingNames;
    }

    public function getQueryNames(): array
    {
        return $this->queryNames;
    }

    public function comprehendPipes(Dialog $current): ? array
    {
        return $this->comprehendPipes;
    }


    /*------ relations -------*/

    public function asMemoryDef() : MemoryDef
    {
        return $this->_asMemoryDef
            ?? $this->_asMemoryDef = new IMemoryDef(new MemoryMeta([
                'name' => $this->name,
                'title' => $this->title,
                'desc' => $this->desc,
                'scopes' => $this->memoryScopes,
                'attrs' => $this->memoryAttrs,
            ]));
    }

    public function asStageDef() : StageDef
    {
        return $this->_asStageDef
            ?? $this->_asStageDef = new InitStage([
                'name' => $this->name,
                'contextName' => $this->name,
                'title' => $this->title,
                'desc' => $this->desc,
                'stageName' => '',
                'asIntent' => $this->asIntent,
            ]);
    }


    /*------ to context -------*/

    public function wrapContext(Cloner $cloner, Ucl $ucl): Context
    {
        return call_user_func(
            [$this->contextWrapper, Context::CREATE_FUNC],
            $cloner,
            $ucl
        );
    }

    /*------ redirect -------*/

    public function onRedirect(Dialog $prev, Ucl $current): ? Operator
    {
        $redirect = $this->ifRedirect;

        if (isset($redirect)) {
            return $prev
                ->container()
                ->action($redirect, ['prev' => $prev, 'current' => $current]);
        }

        return null;
    }


    /*------ stages -------*/

    public function eachPredefinedStage(): \Generator
    {
        foreach ($this->getStageMetaMap() as $stageMeta) {
            yield $stageMeta->toWrapper();
        }
    }

    public function getPredefinedStageNames(bool $isFullname = false): array
    {
        return array_map(
            function(StageMeta $meta) use ($isFullname) {
                if ($isFullname) {
                    return $meta->name;
                }

                return $meta->stageName;
            },
            $this->stages
        );
    }


    /**
     * @return StageMeta[]
     */
    protected function getStageMetaMap() : array
    {
        if (isset($this->_stageMetaMap)) {
            return $this->_stageMetaMap;
        }

        foreach ($this->stages as $stageMeta) {
            $shortName = $stageMeta->stageName;
            $this->_stageMetaMap[$shortName] = $stageMeta;
        }

        return $this->_stageMetaMap;
    }

    public function getPredefinedStage(string $name): ? StageDef
    {
        $meta = $this->getStageMetaMap()[$name] ?? null;
        return isset($meta)
            ? $meta->toWrapper()
            : null;
    }


    public function firstStage(): ? string
    {
        return $this->firstStage;
    }

    public function __destruct()
    {
        $this->_asMemoryDef = null;
        $this->_asStageDef = null;
        parent::__destruct();
    }

}