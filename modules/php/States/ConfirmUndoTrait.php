<?php

namespace STIG\States;

use STIG\Helpers\Log;
use STIG\Managers\Players;
use STIG\Core\Notifications;
use STIG\Core\Globals;
use STIG\Core\PGlobals;
use STIG\Exceptions\UnexpectedException;

trait ConfirmUndoTrait
{
    public function addCheckpoint($stateId,$pId = 0)
    {
        if($pId>0){
            PGlobals::setEngineChoices($pId, 0);
            PGlobals::setState($pId, $stateId);
        }
        Log::checkpoint($stateId,$pId);
    }

    public function addStep($pId, $stateId)
    {
        PGlobals::setState($pId, $stateId);
        $stepId = Log::step($stateId);
        PGlobals::incEngineChoices($pId, 1);
    }

    public function argsConfirmTurn($pId)
    {
        $data = [];
        $this->addArgsForUndo($pId, $data);
        return $data;
    }
        
    function addArgsForUndo($pId, &$args)
    {
        $args['previousSteps'] = Log::getUndoableSteps($pId);
        $args['previousChoices'] = PGlobals::getEngineChoices($pId);
    }

    function stConfirmChoices()
    {
        $player = Players::getCurrent();
        $pId = $player->getId();
        $this->gamestate->nextPrivateState($pId,'confirm');
    }

    public function stConfirmTurn()
    {
        /*
        if (Globals::getChoices() == 0) {
            $this->actConfirmTurn(true);
        }
        */
    }

    public function actConfirmTurn($auto = false)
    {
        if (!$auto) {
            self::checkAction('actConfirmTurn');
        }
        $player = Players::getCurrent();
        $pId = $player->getId();
        $this->addCheckpoint($player->getPrivateState(),$pId);
        $this->gamestate->nextPrivateState($pId,'confirm');
    }


    public function actRestart()
    {
        self::checkAction('actRestart');
        $player = Players::getCurrent();
        $pId = $player->id;
        if (PGlobals::getEngineChoices($pId) < 1) {
            throw new UnexpectedException(404,'No choice to undo');
        }
        Log::undoTurn($pId);
        Notifications::restartTurn($player);
    }

    public function actUndoToStep($stepId)
    {
        self::checkAction('actRestart');
        $player = Players::getCurrent();
        Log::undoToStep($player->id,$stepId);
        Notifications::undoStep($player, $stepId);
    }
}
