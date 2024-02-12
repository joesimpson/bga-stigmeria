<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Helpers\Collection;
use STIG\Managers\Players;
use STIG\Managers\Schemas;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;
use STIG\Models\TokenCoord;

trait WindEffectTrait
{
  
  public function stWindEffect()
  {
    self::trace("stWindEffect()");
    $players = Players::getAll();

    $turn = Globals::getTurn();
    if($turn == TURN_MAX && Globals::isModeNormal() ){
      //In this mode, this is the last played turn and no wind is blowing
      $this->gamestate->nextState('next');
      return;
    }

    //LOOP ON EACH player BOARD + central board
    if(!Globals::isModeNoCentralBoard()) $this->doWindEffect($turn);
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
     
    $windDir = Globals::getWindDir($turn);
    self::trace("doWindEffect($turn) : wind blows to $windDir");

    if(isset($player)){
      $boardTokens = Tokens::getAllOnPersonalBoard($player->id) ;
    }
    else {
      $boardTokens = Tokens::getAllOnCentralBoard();
    }

    $movedTokens = [];

    switch($windDir){
      case WIND_DIR_SOUTH:
        //FROM North to South : read last row first
        for($row = ROW_MAX; $row>=ROW_MIN; $row-- ){
          for($col = COLUMN_MIN; $col<=COLUMN_MAX; $col++ ){
            $token = Tokens::findTokenOnBoardWithCoord($boardTokens,$row,$col );
            if(isset($token) && $this->doWindBlowsTo($token,$windDir,$player,$boardTokens) ){
              //if token moved, add it in array which will be sent to client
              $movedTokens[] = $token;
            }
          }
        }
        break;
      //TODO JSA ALL DIRS
      default: 
        Notifications::message("Wind direction $windDir not supported !");
        return;
    }
    Notifications::windBlows($windDir,new Collection($movedTokens),$player);
  }

  /**
   * @param StigmerianToken $token
   * @param string $windDir
   * @param Player $player
   * @param Collection $boardTokens
   * @return bool true if token has moved
   */
  public function doWindBlowsTo($token,$windDir,$player,$boardTokens)
  {
    //self::trace("doWindBlowsTo($windDir)");

    if($token->isPollen()) return false;

    switch($windDir){
      case WIND_DIR_SOUTH:
        //FROM North to South : 
        $futureCoord = new TokenCoord($token->getType(), $token->getRow() + 1, $token->getCol());
        break;
      case WIND_DIR_NORTH:
        $futureCoord = new TokenCoord($token->getType(), $token->getRow() - 1, $token->getCol());
        break;
      case WIND_DIR_EAST:
        $futureCoord = new TokenCoord($token->getType(), $token->getRow(), $token->getCol() + 1);
        break;
      case WIND_DIR_WEST:
        $futureCoord = new TokenCoord($token->getType(), $token->getRow(), $token->getCol() - 1);
        break;
      default: 
        return false;
    }
    //TODO JSA PERFS : use a single update for all tokens
    $other = Tokens::findTokenOnBoardWithCoord($boardTokens,$futureCoord->row,$futureCoord->col );
    if(isset($other) ){
      //Place is not empty
      return false;
    }
    $token->updateCoord($futureCoord);

    if($token->isOutOfGrid()){
      self::trace("doWindBlowsTo($windDir) token is out of grid :".json_encode($token));
      if($token->getPId() !== null ) Stats::inc("tokens_board",$token->getPId(), -1);
      //TODO JSA ELIMINATE player WHEN NORMAL MODE
      
      //TODO JSA ELSE MOVE it or DELETE it ?
      $token->setLocation(TOKEN_LOCATION_OUT);
      $token->setPId(null);
      $token->setRow(null);
      $token->setCol(null);
      
    }
    else {
      //self::trace("doWindBlowsTo($windDir) token is still in grid :".json_encode($token));
      $token->checkAndBecomesPollen($player);
    }
    return true;
  }
}
