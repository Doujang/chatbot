<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Blueprint\Ghost\MindDef;


/**
 * @author thirdgerb <thirdgerb@gmail.com>
 */
interface ChatDef extends Def
{

    public function getIndex() : string;

    public function getCid() : string;

    public function getSay() : string;

    public function getReply() : string;

}