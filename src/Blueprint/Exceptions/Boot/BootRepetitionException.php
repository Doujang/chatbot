<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Blueprint\Exceptions\Boot;

use Commune\Blueprint\Exceptions\CommuneBootingException;


/**
 * 机器人重复启动异常.
 * @author thirdgerb <thirdgerb@gmail.com>
 */
class BootRepetitionException extends CommuneBootingException
{

}