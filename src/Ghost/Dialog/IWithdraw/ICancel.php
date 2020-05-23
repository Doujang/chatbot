<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Ghost\Dialog\IWithdraw;

use Commune\Blueprint\Ghost\Runtime\Operator;
use Commune\Ghost\Dialog\AbsWithdraw;
use Commune\Blueprint\Ghost\Dialog\Withdraw\Cancel;

/**
 * @author thirdgerb <thirdgerb@gmail.com>
 */
class ICancel extends AbsWithdraw implements Cancel
{
    protected function runTillNext(): Operator
    {
        return $this->withdrawCurrent();
    }


}