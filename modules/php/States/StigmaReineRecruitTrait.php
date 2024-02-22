<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Helpers\GridUtils;
use STIG\Managers\Players;
use STIG\Managers\Tokens;

trait StigmaReineRecruitTrait
{
  public function argSRecruit($pId)
  { 
    $tokens = Tokens::getAllCentralRecruits();
    $coordName = GridUtils::getCoordName(FIRST_TOKEN_ROW,FIRST_TOKEN_COLUMN);
    $args  = [
      'L' => $coordName,
      'n' => ACTION_COST_CENTRAL_RECRUIT,
      'p' => $tokens,
    ];
    return $args;
  }
  
  /**
   * @param int $tokenId
   */
  public function actSRecruitToken($tokenId)
  {
      self::checkAction( 'actSRecruitToken' ); 
      self::trace("actSRecruitToken($tokenId)");

      $player = Players::getCurrent();
      $pId = $player->id;
      $this->addStep( $pId, ST_TURN_CHOICE_RECRUIT_CENTRAL);

      $actionCost = ACTION_COST_CENTRAL_RECRUIT;
      if($player->countRemainingPersonalActions() < $actionCost){
          throw new UnexpectedException(10,"Not enough actions to do that");
      }
      $token = Tokens::get($tokenId);
      if($token->location != TOKEN_LOCATION_CENTRAL_RECRUIT ){
          throw new UnexpectedException(20,"You cannot take this token");
      } 

      //EFFECT : PLACE the TOKEN 
      $token->moveToRecruitZone($player,$actionCost);
      
      $player->incNbPersonalActionsDone($actionCost);
      Notifications::useActions($player);
      $player->giveExtraTime();
      Stats::inc("actions_c4",$player->getId());

      $this->gamestate->nextPrivateState($player->id, "next");
  }
}
