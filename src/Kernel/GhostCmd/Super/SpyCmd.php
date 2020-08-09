<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Kernel\GhostCmd\Super;

use Commune\Blueprint\CommuneEnv;
use Commune\Kernel\GhostCmd\AGhostCmd;
use Commune\Framework\Spy\SpyAgency;
use Commune\Blueprint\Framework\Command\CommandMsg;
use Commune\Blueprint\Framework\Pipes\RequestCmdPipe;
use Commune\Support\Markdown\MarkdownUtils;

/**
 * @author thirdgerb <thirdgerb@gmail.com>
 */
class SpyCmd extends AGhostCmd
{
    const SIGNATURE = 'spy';

    const DESCRIPTION = '查看一些关键类的实例数量. 用于排查部分内存泄露问题.';

    protected function handle(CommandMsg $message, RequestCmdPipe $pipe): void
    {
        if (! CommuneEnv::isDebug()) {
            $this->error('spy agency is only running at debug mode');
            return;
        }

        $classes = SpyAgency::getSpies();
        $str = '';
        foreach ($classes as $running => $count) {
            $str .= $this->showRunningTrace(
                $running,
                $count
            );
        }

        $this->info(MarkdownUtils::quote($str));
    }

    protected function showRunningTrace(string $type, int $count) : string
    {
        $output = "\n$type 运行中实例共 $count 个";
        return $output;
    }


}