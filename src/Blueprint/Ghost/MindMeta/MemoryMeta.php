<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Blueprint\Ghost\MindMeta;

use Commune\Support\Option\Wrapper;
use Commune\Ghost\IMindDef\IMemoryDef;
use Commune\Ghost\Support\ContextUtils;
use Commune\Support\Option\AbsOption;
use Commune\Blueprint\Ghost\MindDef\MemoryDef;

/**
 * @author thirdgerb <thirdgerb@gmail.com>
 *
 * @property string $name              当前配置的 ID
 * @property string $title             标题
 * @property string $desc              简介
 * @property string[] $scopes          记忆的作用域.
 * @property array $attrs              默认值, 也是记忆体数据的样板.
 *
 */
class MemoryMeta extends AbsOption implements DefMeta
{
    const IDENTITY = 'name';

    public static function stub(): array
    {
        return [
            'name' => '',
            'title' => '',
            'desc' => '',
            'scopes' => [],
            'attrs' => [],
        ];
    }

    public static function relations(): array
    {
        return [];
    }

    public static function validate(array $data): ? string /* errorMsg */
    {
        $name = $data['name'] ?? '';

        if (!ContextUtils::isValidMemoryName($name)) {
            return "memory name $name is invalid";
        }

        return parent::validate($data);
    }

    /**
     * @return MemoryDef
     */
    public function toWrapper(): Wrapper
    {
        return IMemoryDef::wrapMeta($this);
    }


}