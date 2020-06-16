<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Blueprint\Kernel\Handlers;

use Commune\Blueprint\Kernel\Protocals\GhostRequest;
use Commune\Blueprint\Kernel\Protocals\ShellInputResponse;
use Commune\Blueprint\Kernel\Protocals\ShellOutputRequest;
use Commune\Blueprint\Kernel\Protocals\ShellOutputResponse;

/**
 * @author thirdgerb <thirdgerb@gmail.com>
 */
interface Shell2GhostMessenger extends AppProtocalHandler
{

    /**
     * @param ShellInputResponse $protocal
     * @return GhostRequest|ShellOutputResponse|ShellOutputRequest
     */
    public function __invoke($protocal);

}