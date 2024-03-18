<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;

trait SoloGainSpecialTrait
{
  public function argSoloGainSP()
  { 
    $playerId = Globals::getFirstPlayer();
    
    $lockedActions = $this->listPossibleNewSpAction($playerId);
    $nbRemaining = PGlobals::getNbSpActions($playerId);
    $nbGains = PGlobals::getNbSpActionsMax($playerId);

    $args = [
      'a' => $lockedActions,
      'n' => $nbRemaining,
      'n2' => $nbGains,
    ];
    return $args;
  } 
  
  /**
   * @param int $actionType
   */
  public function actSoloChooseSp($actionType)
  {
    self::checkAction( 'actSoloChooseSp' ); 
    self::trace("actSoloChooseSp($actionType)");
    
    $player = Players::getCurrent();
    $pId = $player->id;
    $this->addStep( $pId, ST_SOLO_CHOICE_SP);

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

    $this->gamestate->nextState("next");
  }
}
