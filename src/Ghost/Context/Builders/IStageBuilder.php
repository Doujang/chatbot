<?php

/**
 * This file is part of CommuneChatbot.
 *
 * @link     https://github.com/thirdgerb/chatbot
 * @document https://github.com/thirdgerb/chatbot/blob/master/README.md
 * @contact  <thirdgerb@gmail.com>
 * @license  https://github.com/thirdgerb/chatbot/blob/master/LICENSE
 */

namespace Commune\Ghost\Context\Builders;

use Commune\Blueprint\Ghost\Context\StageBuilder;
use Commune\Blueprint\Ghost\Dialog;
use Commune\Blueprint\Ghost\MindDef\StageDef;
use Commune\Blueprint\Ghost\Operate\Operator;
use Commune\Framework\Spy\SpyAgency;

/**
 * @author thirdgerb <thirdgerb@gmail.com>
 */
class IStageBuilder implements StageBuilder
{
    /**
     * @var Operator|null
     */
    protected $operator;

    /**
     * @var Dialog
     */
    public $dialog;

    /**
     * @var StageDef
     */
    public $def;

    /**
     * @var bool
     */
    protected $redirect;

    public function __construct(Dialog $dialog, StageDef $stageDef, bool $redirect)
    {
        $this->dialog = $dialog;
        $this->def = $stageDef;
        $this->redirect = $redirect;

        SpyAgency::incr(static::class);
    }

    public function always($caller): StageBuilder
    {
        if (isset($this->operator) || $this->redirect) {
            unset($caller);
            return  $this;
        }
        $this->operator = $this->dialog->container()->call($caller);
        unset($caller);
        return $this;
    }


    public function onRedirect($caller): StageBuilder
    {
        if (isset($this->operator)) {
            unset($caller);
            return  $this;
        }

        if ($this->redirect) {
            $this->operator = $this->dialog->container()->action($caller);
        }

        return  $this;
    }

    public function onActivate($caller): StageBuilder
    {
        if (isset($this->operator) || $this->redirect) {
            unset($caller);
            return $this;
        }

        if ($this->dialog->isEvent(Dialog::ACTIVATE)) {
            $ioc = $this->dialog->container();
            $this->operator = $ioc->action($caller);
        }

        return  $this;
    }

    public function onReceive($caller): StageBuilder
    {
        if (isset($this->operator) || $this->redirect) {
            return $this;
        }

        if ($this->dialog->isEvent(Dialog::RECEIVE)) {
            $this->operator = $this->dialog->container()->action($caller);
        }

        return  $this;
    }

    public function onResume($caller): StageBuilder
    {
        if (isset($this->operator) || $this->redirect) {
            return $this;
        }

        if ($this->dialog->isEvent(Dialog::RESUME)) {
            $this->operator = $this->dialog->container()->action($caller);
        }

        return $this;
    }


    public function onEvent(string $event, $caller): StageBuilder
    {
        if (isset($this->operator) || $this->redirect) {
            return $this;
        }

        if ($this->dialog->isEvent($event)) {
            $this->operator = $this->dialog->container()->action($caller);
        }

        return  $this;
    }

    public function onEventExcept($caller, string ...$events) : StageBuilder
    {
        if (isset($this->operator) || $this->redirect) {
            return $this;
        }

        foreach ($events as $event) {
            if ($this->dialog->isEvent($event)) {
                return $this;
            }
        }

        $this->operator = $this->dialog->container()->action($caller);
        return $this;
    }

    public function otherwise($caller): StageBuilder
    {
        if (isset($this->operator)) {
            return $this;
        }

        $this->operator = $this->dialog->container()->action($caller);
        return $this;
    }


    public function popOperator() : ? Operator
    {
        $operator = $this->operator;
        unset($this->operator);
        return $operator;
    }

    public function __destruct()
    {
        unset($this->operator);
        unset($this->dialog);
        SpyAgency::decr(static::class);
    }

    public function onCancel(string $stageName): StageBuilder
    {
        if ($this->dialog->isEvent(Dialog::ACTIVATE)) {
            $this->dialog->context->getTask()->onCancel($stageName);
        }
        return $this;
    }

    public function onQuit(string $stageName): StageBuilder
    {
        if ($this->dialog->isEvent(Dialog::ACTIVATE)) {
            $this->dialog->context->getTask()->onQuit($stageName);
        }
        return $this;
    }


}