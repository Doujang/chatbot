<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Framework\Blueprint\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * @author thirdgerb <thirdgerb@gmail.com>
 */
interface CommandDef
{
    public function getCommandName() : string;

    public function toCommandMessage(string $cmdText) : CommandMsg;

    /**
     * @return InputArgument[]
     */
    public function getArguments() : array;

    /**
     * @return InputOption[]
     */
    public function getOptions() : array;
}