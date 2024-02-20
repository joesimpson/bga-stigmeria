<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
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
        $nbRemaining = Globals::getNbSpActions();
        $nbGains = Globals::getNbSpActionsMax();

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
 
        $nbRemaining = Globals::getNbSpActions();
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
        Globals::setNbSpActions($nbRemaining);
        Notifications::unlockSp($player,$action);

        if($nbRemaining >=1){
            $this->gamestate->nextPrivateState($pId, "continue");
            return;
        }
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
