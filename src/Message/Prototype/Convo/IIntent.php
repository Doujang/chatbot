<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Message\Prototype\Convo;

use Commune\Message\Blueprint\Convo\IntentMsg;
use Commune\Message\Prototype\AMessage;

/**
 * @author thirdgerb <thirdgerb@gmail.com>
 */
class IIntent extends AMessage implements IntentMsg
{

    protected $entities;

    protected $intentName;

    public function __construct(
        string $intentName = '',
        array $entities = [],
        float $createdAt = null
    )
    {
        $this->intentName = $intentName;
        $this->entities = $entities;
        parent::__construct($createdAt);
    }

    public function __sleep(): array
    {
        return [
            'intentName',
            'entities',
            'createdAt',
        ];
    }

    public function getIntentName(): string
    {
        return $this->intentName;
    }

    public function getEntities(): array
    {
        return $this->entities;
    }

    public function isEmpty(): bool
    {
        return $this->intentName === '';
    }


}