<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Chatbot\Ghost\Blueprint\Callables;

use Commune\Chatbot\Ghost\Blueprint\Redirector;

/**
 * @author thirdgerb <thirdgerb@gmail.com>
 */
interface Action
{


    public function __invoke() : Redirector;
}