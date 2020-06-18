<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Contracts\Messenger;

use Commune\Protocals\Intercom\InputMsg;
use Commune\Protocals\Intercom\OutputMsg;
use Commune\Protocals\IntercomMsg;


/**
 * @author thirdgerb <thirdgerb@gmail.com>
 */
interface MessageDB
{

    /**
     * 记录一组消息. 通常是一个异步或者协程行为.
     * @param InputMsg $input
     * @param OutputMsg ...$outputs
     */
    public function recordBatch(InputMsg $input, OutputMsg ...$outputs) : void;

    /**
     * @param callable $fetcher
     * @return IntercomMsg[]
     */
    public function fetch(callable $fetcher) : array;


    /**
     * 按条件获取若干消息
     * @return Condition
     */
    public function where() : Condition;

    /**
     * 获取消息.
     * @param string $messageId
     * @return IntercomMsg|null
     */
    public function find(string $messageId) : ? IntercomMsg;
}