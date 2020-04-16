<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Ghost\Prototype\OperatorsBack\Staging;

use Commune\Ghost\Blueprint\Convo\Conversation;
use Commune\Ghost\Blueprint\Definition\StageDef;
use Commune\Ghost\Blueprint\Operator\Operator;
use Commune\Ghost\Blueprint\Runtime\Node;
use Commune\Ghost\Prototype\OperatorsBack\AbsOperator;
use Commune\Ghost\Prototype\Stage\IActivateStage;

/**
 * @author thirdgerb <thirdgerb@gmail.com>
 */
class WakeStage extends AbsOperator
{
    /**
     * @var StageDef
     */
    protected $stageDef;

    /**
     * @var Node
     */
    protected $node;

    public function __construct(StageDef $stageDef, Node $node)
    {
        $this->stageDef = $stageDef;
        $this->node = $node;
    }

    public function invoke(Conversation $conversation): ? Operator
    {
        $activateStage = new IActivateStage(
            $conversation,
            $this->stageDef,
            $this->node
        );

        return $this->stageDef->onWake($activateStage);
    }
}