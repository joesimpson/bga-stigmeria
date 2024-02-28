<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;
use STIG\Managers\Schemas;
use STIG\Models\PlayerAction;

trait CentralGainSpecialTrait
{
    public function argGainSpecialAction($playerId)
    {
        $lockedActions = $this->listPossibleNewSpAction($playerId);
        $nbRemaining = PGlobals::getNbSpActions($playerId);
        $nbGains = PGlobals::getNbSpActionsMax($playerId);

        $args = [
            'a' => $lockedActions,
            'n' => $nbRemaining,
            'n2' => $nbGains,
        ];
        $this->addArgsForUndo($playerId, $args);
        return $args;
    }
      
    /**
     * @param int $actionType
     */
    public function actChooseSp($actionType)
    {
        self::checkAction( 'actChooseSp' ); 
        self::trace("actChooseSp($actionType)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep( $pId, $player->getPrivateState());
 
        $nbRemaining = PGlobals::getNbSpActions($pId);
        if($nbRemaining < 1){
            throw new UnexpectedException(450,"You cannot select another action");
        }
        if(!$this->canGainSpecialAction($actionType, $pId)){
            throw new UnexpectedException(450,"You cannot select this action");
        }

        $action = PlayerActions::createAction([
            'type'=>$actionType,
            'location'=>ACTION_LOCATION_PLAYER_BOARD,
            'player_id'=>$pId,
            'state' => PlayerActions::getInitialState($actionType),
        ]);
        $nbRemaining--;
        PGlobals::setNbSpActions($pId,$nbRemaining);
        Notifications::unlockSp($player,$action);
        Stats::inc("unlocked_sp",$pId);

        $fromState = PGlobals::getLastDriftPreviousState($pId);
        if(isset($fromState) && $fromState!='null'){
            PGlobals::setLastDriftPreviousState($pId, null);
            //If coming from last drift result -> end this step
            if('INACTIVE' == $fromState){
                $this->gamestate->setPlayerNonMultiactive( $pId, 'end' );
                return;
            }
            else if(is_int($fromState) && $fromState >0) {
                PGlobals::setState($pId, $fromState);
                $this->gamestate->setPrivateState($pId, $fromState);
                return;
            }
        }
        
        if($nbRemaining >=1){
            $this->gamestate->nextPrivateState($pId, "continue");
            return;
        }
        
        PGlobals::setState($pId, ST_TURN_CENTRAL_TOKEN_DISTRIBUTION);
        $this->gamestate->nextPrivateState($pId, "next");
    }

    /**
     * @param int $actionType
     * @param int $playerId
     */
    public function canGainSpecialAction($actionType, $playerId)
    {
        //Check action not already set
        if(PlayerActions::getPlayer($playerId, [$actionType])->count() > 0) return false;
        
        //Difficulty filter
        $minDiff = PlayerActions::getDifficulty($actionType);
        $currentDiff = Schemas::getCurrentSchema()->difficulty;
        if($minDiff > $currentDiff) return false;

        return true;
    }

    public function listPossibleNewSpAction($playerId){
        $lockedActions = [];
        foreach(ACTION_TYPES as $type){
            //TODO JSA PERFS read all once 
            if($this->canGainSpecialAction($type, $playerId)){
                $lockedActions[] = $type;
            }
        }
        return $lockedActions;
    }
}
