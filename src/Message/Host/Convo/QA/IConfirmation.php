<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Message\Host\Convo\QA;

use Commune\Protocals\HostMsg\Convo\QA\Confirmation;


/**
 * @author thirdgerb <thirdgerb@gmail.com>
 */
class IConfirmation extends IAnswerMsg implements Confirmation
{
    public function isPositive(): bool
    {
        return strval($this->choice) === IConfirm::POSITIVE_INDEX;
    }

    public function isNegative(): bool
    {
        return strval($this->choice) === IConfirm::NEGATIVE_INDEX;
    }


}