<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;
use STIG\Managers\Schemas;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

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

    //Beware ! pollen are not moved !
    if(isset($player)){
      $boardTokens = Tokens::getAllOnPersonalBoard($player->id)
        ->filter( function ($token) {return !$token->isPollen(); });
    }
    else {
      $boardTokens = Tokens::getAllOnCentralBoard()
        ->filter( function ($token) {return !$token->isPollen(); });
    }

    switch($windDir){
      case WIND_DIR_SOUTH:
        //FROM North to South : read last row first
        for($row = ROW_MAX; $row>=ROW_MIN; $row-- ){
          for($col = COLUMN_MIN; $col<=COLUMN_MAX; $col++ ){
            $token = Tokens::findTokenOnBoardWithCoord($boardTokens,$row,$col );
            if(isset($token) && !$this->doWindBlowsTo($token,$windDir,$player) ){
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
   * @param StigmerianToken $token
   * @param string $windDir
   * @param Player $player
   * @return bool true if token has moved
   */
  public function doWindBlowsTo($token,$windDir,$player)
  {
    //self::trace("doWindBlowsTo($windDir)");

    //TODO JSA CHECK NO POLLEN (or token) is in future position BEFORE moving !

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
      //self::trace("doWindBlowsTo($windDir) token is still in grid :".json_encode($token));
        
      if(Schemas::matchCurrentSchema($token)){
        $token->becomesPollen($player);
      }
    }
    return true;
  }
}
