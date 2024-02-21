<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;
use STIG\Models\PlayerAction;

trait CentralGainSpecialTrait
{
    public function argGainSpecialAction($playerId)
    {

        foreach(ACTION_TYPES as $type){
            if($this->canGainSpecialAction($type, $playerId)){
                $lockedActions[] = $type;
            }
        }
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
        if(PlayerActions::getPlayer($playerId, [$actionType])->count() > 0) return false;
        //TODO JSA filter difficulty

        return true;
    }
}
