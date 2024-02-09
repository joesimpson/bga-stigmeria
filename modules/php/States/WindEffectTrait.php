<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;
use STIG\Managers\Tokens;

trait WindEffectTrait
{
  
  public function stWindEffect()
  {
    self::trace("stWindEffect()");
    $players = Players::getAll();

    $turn = Globals::getTurn();
    //LOOP ON EACH player BOARD + central board
    $this->doWindEffect($turn);
    foreach($players as $playerId => $player){
      $this->doWindEffect($turn,$player);
    }

    $this->gamestate->nextState('next');
  }
  
  /**
   * @param int $turn 
   * @param Player $player
   */
  public function doWindEffect($turn,$player = null)
  {
    self::trace("doWindEffect($turn)");
     
    $getterName = "getWindDirection$turn";
    $windDir = Globals::$getterName();
    self::trace("doWindEffect($turn) : wind blows to $windDir");

    if(isset($player)){
      $boardTokens = Tokens::getAllOnPersonalBoard($player->id);
    }
    else {
      $boardTokens = Tokens::getAllOnCentralBoard();
    }

    switch($windDir){
      case WIND_DIR_SOUTH:
        //FROM North to South : read last row first
        for($row = ROW_MAX; $row>=ROW_MIN; $row-- ){
          for($col = COLUMN_MIN; $col<=COLUMN_MAX; $col++ ){
            $token = Tokens::findTokenOnBoardWithCoord($boardTokens,$row,$col );
            if(isset($token) && !$this->doWindBlowsTo($token,$windDir) ){
              //TODO JSA if token not moved, remove it from array which will be sent to client
            }
          }
        }
        break;
      //TODO JSA ALL DIRS
      default: 
        Notifications::message("Wind direction $windDir not supported !");
        return;
    }
    Notifications::windBlows($windDir,$boardTokens,$player);
  }

  /**
   * @return bool true if token has moved
   */
  public function doWindBlowsTo($token,$windDir)
  {
    //self::trace("doWindBlowsTo($windDir)");

    switch($windDir){
      case WIND_DIR_SOUTH:
        //FROM North to South : 
        $token->incRow(1);
        break;
      case WIND_DIR_NORTH:
        $token->incRow(-1);
        break;
      case WIND_DIR_EAST:
        $token->incCol(1);
        break;
      case WIND_DIR_WEST:
        $token->incCol(-1);
        break;
      default: 
        return false;
    }
    //TODO JSA PERFS : use a single update for all tokens

    if($token->isOutOfGrid()){
      self::trace("doWindBlowsTo($windDir) token is out of grid :".json_encode($token));
      //TODO JSA ELIMINATE player WHEN NORMAL MODE

      //TODO JSA ELSE MOVE it or DELETE it ?
      $token->setLocation(TOKEN_LOCATION_OUT);
      $token->setPId(null);
      $token->setRow(null);
      $token->setCol(null);
    }
    else {
      self::trace("doWindBlowsTo($windDir) token is still in grid :".json_encode($token));
    }
    return true;
  }
}
