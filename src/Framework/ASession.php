<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Framework;

use Commune\Blueprint\CommuneEnv;
use Commune\Blueprint\Framework\ReqContainer;
use Commune\Blueprint\Framework\Session;
use Commune\Framework\Exceptions\SerializeForbiddenException;
use Commune\Framework\Spy\SpyAgency;
use Commune\Support\Pipeline\OnionPipeline;
use Commune\Support\Uuid\HasIdGenerator;
use Commune\Support\Uuid\IdGeneratorHelper;


/**
 * @author thirdgerb <thirdgerb@gmail.com>
 */
abstract class ASession implements Session, HasIdGenerator
{
    use IdGeneratorHelper;

    const SINGLETONS = [
    ];

    /**
     * @var ReqContainer
     */
    protected $_container;

    /**
     * @var string
     */
    protected $sessionId;

    /*------ cached ------*/

    /**
     * @var string[]
     */
    protected $listened = [];

    /**
     * Session 级别的单例.
     * @var array
     */
    protected $singletons = [];

    /**
     * @var string
     */
    protected $traceId;


    /**
     * ASession constructor.
     * @param ReqContainer $container
     * @param string $sessionId
     */
    public function __construct(ReqContainer $container, string $sessionId = null)
    {
        $this->_container = $container;
        $this->traceId = $container->getId();
        // 允许为 null
        $this->sessionId = $sessionId ?? $this->createUuId();

        $container->share(Session::class, $this);
        SpyAgency::incr(static::class);
    }


    /*------ id ------*/

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /*------ abstract ------*/

    abstract protected function flushInstances() : void;

    /**
     * 返回运行的步骤, 方便 debug
     * @return string[]
     */
    abstract protected function saveSession() : array;

    /*------ components ------*/

    public function getContainer(): ReqContainer
    {
        return $this->_container;
    }


    /*------ logic ------*/


    public function buildPipeline(array $pipes, string $via, \Closure $destination): \Closure
    {
        $pipeline = new OnionPipeline($this->_container);
        $pipeline->through(...$pipes);
        $pipeline->via($via);
        return $pipeline->buildPipeline($destination);
    }


    /*------ status ------*/

    public function getTraceId(): string
    {
        return $this->traceId;
    }

    public function getAppId(): string
    {
        return $this->getApp()->getId();
    }


    /*------ event ------*/

    public function fire(Session\SessionEvent $event): void
    {
        $id = $event->getEventName();
        if (!isset($this->listened[$id])) {
            return;
        }

        // 执行所有的事件.
        foreach ($this->listened[$id] as $handler) {
            call_user_func($handler, $this, $event);
        }
    }

    public function listen(string $eventName, callable $handler): void
    {
        $this->listened[$eventName][] = $handler;
    }



   /*------ getter ------*/

    public function __get($name)
    {
        $injectable = static::SINGLETONS[$name] ?? null;

        if (!empty($injectable)) {
            return $this->singletons[$name]
                ?? $this->singletons[$name] = $this->_container->get($injectable);
        }

        return null;
    }

    public function isSingletonInstanced($name) : bool
    {
        $injectable = static::SINGLETONS[$name] ?? null;

        return isset($injectable)
            ? isset($this->singletons[$name])
            : false;
    }

    public function __isset($name)
    {
        return isset(static::SINGLETONS[$name]);
    }

    /*------ finish ------*/

    public function finish(): void
    {
        $steps = $this->saveSession();

        // 记录运行日志方便排查问题.
        $debug = CommuneEnv::isDebug();
        if ($debug && !empty($steps)) {
            $log = implode('|', $steps);
            $this->getLogger()->debug(
                static::class
                . '::'
                . __FUNCTION__
                . ": $log"
            );
        }


        $container = $this->_container;

        $this->flushInstances();

        // container
        unset(
            $this->listened,
            $this->singletons,
            $this->_container
        );

        $container->destroy();
    }


    public function __sleep()
    {
        throw new SerializeForbiddenException(static::class);
    }


    public function __destruct()
    {
        SpyAgency::decr(static::class);
    }
}