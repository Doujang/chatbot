<?php


namespace Commune\Chatbot\OOHost\Directing\Stage;

use Commune\Chatbot\OOHost\Directing\AbsNavigator;
use Commune\Chatbot\OOHost\Directing\Navigator;

/**
 * 运行当前的stage
 */
class StartStage extends AbsNavigator
{
    public function doDisplay(): ? Navigator
    {
        $context = $this->history->getCurrentContext();
        $stage = $this->history->currentTask()->getStage();

        return $context
            ->getDef()
            ->startStage(
                $context,
                $this->dialog,
                $stage
            );
    }


}