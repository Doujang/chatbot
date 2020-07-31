<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Blueprint\Ghost\Memory;

use Commune\Blueprint\Ghost\Cloner;
use Commune\Blueprint\Ghost\Context;

/**
 * 静态的回忆工具, 用静态方法来 定义|获取  记忆体.
 *
 * 是一种比较简便的做法.
 *
 * @author thirdgerb <thirdgerb@gmail.com>
 */
interface Recall extends Recollection
{

    /**
     * @param Cloner $cloner
     * @param string|null $id
     * @return static
     */
    public static function find(Cloner $cloner, string $id = null) : Recall;

    /**
     * @param Context $context
     * @return static
     */
    public static function from(Context $context) : Recall;
}