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

    public function argConfirmChoices()
    {
        $player = Players::getCurrent();
        $pId = $player->id;
        $data = [];
        //TODO JSA see how to Undo Charmer
        //$this->addArgsForUndo($pId, $data);
        return $data;
    }
    
    //When private state
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

    public function actConfirmTurn($auto = false)
    {
        if (!$auto) {
            self::checkAction('actConfirmTurn');
        }
        $player = Players::getCurrent();
        $pId = $player->getId();
        /*
        $this->addCheckpoint($player->getPrivateState(),$pId);
        $this->gamestate->nextPrivateState($pId,'confirm');
        */
        $this->addCheckpoint(ST_AFTER_TURN);
        $this->gamestate->nextState("confirm");
    }

    public function actRestart()
    {
        self::checkAction('actRestart');
        $player = Players::getCurrent();
        $pId = $player->id;
        if (PGlobals::getEngineChoices($pId) < 1) {
            throw new UnexpectedException(404,'No choice to undo. You may need to reload the page.');
        }
        Log::undoTurn($pId);
        Notifications::restartTurn($player);
    }

    public function actUndoToStep($stepId)
    {
        self::checkAction('actRestart');
        $player = Players::getCurrent();
        $steps = Log::getUndoableSteps($player->id);
        if(!in_array($stepId,$steps)){
            throw new UnexpectedException(404,'This step is not undoable anymore. You may need to reload the page.');
        }
        Log::undoToStep($player->id,$stepId);
        Notifications::undoStep($player, $stepId);
    }
}
