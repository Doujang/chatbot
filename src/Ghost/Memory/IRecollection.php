<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Ghost\Memory;

use Commune\Blueprint\Ghost\Cloner;
use Commune\Blueprint\Ghost\Cloner\ClonerInstanceStub;
use Commune\Blueprint\Ghost\Memory\Memory;
use Commune\Blueprint\Ghost\Memory\Recollection;
use Commune\Support\Arr\ArrayAbleToJson;


/**
 * 标准的记忆体, 可以通过 $cloner->mind->memoryReg()->getDef($name)->recall($cloner) 获取
 * 这样的上下文, 可以通过配置来调用. 更适合基于配置的逻辑定义.
 *
 * @author thirdgerb <thirdgerb@gmail.com>
 */
class IRecollection implements Recollection
{
    use ArrayAbleToJson, TRecollection;

    public function __construct(
        string $id,
        string $name,
        bool $longTerm,
        Memory $memory,
        Cloner $cloner
    )
    {
        $this->_id = $id;
        $this->_name = $name;
        $this->_longTerm = $longTerm;
        $this->_memory = $memory;
        $this->_cloner = $cloner;
    }

    public function toInstanceStub(): ClonerInstanceStub
    {
        return new RecollectionStub(
            $this->_id,
            $this->_name,
            $this->_longTerm
        );
    }

    public function getName(): string
    {
        return $this->_name;
    }


}