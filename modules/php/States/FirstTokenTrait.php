<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Exceptions\UnexpectedException;
use STIG\Helpers\GridUtils;
use STIG\Managers\Players;
use STIG\Managers\Tokens;

trait FirstTokenTrait
{
  public function argFT()
  { 
    $playerId = Globals::getFirstPlayer();
    $possibles = $this->getListPossibleTokensForCentralA5();
    $coordName = GridUtils::getCoordName(FIRST_TOKEN_ROW,FIRST_TOKEN_COLUMN);
    $args  = [
      'L' => $coordName,
      'p' => $possibles,
    ];
    //$this->addArgsForUndo($playerId, $args);
    return $args;
  }
  public function stFT()
  { 
    self::trace("stFT()");

    $this->addCheckpoint(ST_FIRST_TOKEN);
    Notifications::emptyNotif();
  }
  
  /**
   * Select first token to place on common board
   * @param int $typeSource
   */
  public function actFT($typeSource)
  {
      self::checkAction( 'actFT' ); 
      self::trace("actFT($typeSource)");

      $player = Players::getCurrent();
      $pId = $player->id;
      $this->addStep( $player->id, ST_FIRST_TOKEN);
 
      $possibles = $this->getListPossibleTokensForCentralA5();
      if(!in_array($typeSource, $possibles)){
          throw new UnexpectedException(11,"You cannot select this color $typeSource, see ".json_encode($possibles));
      }

      //EFFECT
      $token = Tokens::getAllCentralRecruits()->filter(function($token) use ($typeSource) {
        return $typeSource == $token->getType();
      })->first();
      if(isset($token)){
          //Move central Recruit
          $token->moveToCentralBoard($player,FIRST_TOKEN_ROW,FIRST_TOKEN_COLUMN,0,false);
      } else {
          //CREATE TOKEN IF not from recruits
          $token = Tokens::createToken([
              'type' => $typeSource,
              'location' => TOKEN_LOCATION_CENTRAL_BOARD,
              'y' => FIRST_TOKEN_ROW,
              'x' => FIRST_TOKEN_COLUMN,
          ]);
      }
      Notifications::firstToken($player,$token);

      $this->addCheckpoint(ST_PLAYER_TURN);
      $this->gamestate->nextState("next");
  }
}
