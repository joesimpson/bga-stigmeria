<?php

namespace STIG\Models;

use STIG\Core\Game;
use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Helpers\GridUtils;
use STIG\Managers\Schemas;

/*
 * StigmerianToken: all utility functions concerning a stigmerian token
 */

class StigmerianToken extends \STIG\Helpers\DB_Model
{
  protected $table = 'token';
  protected $primary = 'token_id';
  protected $attributes = [
    'id' => ['token_id', 'int'],
    'state' => ['token_state', 'int'],
    'location' => 'token_location',
    'pId' => ['player_id', 'int'],
    'col' => ['x', 'int'],
    'row' => ['y', 'int'],

    'type' => ['type', 'int'],
  ];
  
  protected $staticAttributes = [
  ];

  public function __construct($row, $datas)
  {
    parent::__construct($row);
    foreach ($datas as $attribute => $value) {
      $this->$attribute = $value;
    }
  }

  /**
   */
  public function getUiData()
  {
    $data = parent::getUiData();
    $data['coord'] = $this->getCoordName();
    $data['pollen'] = $this->isPollen();
    return $data;
  }

  /**
   * @return string Example "J5"
   */
  public function getCoordName()
  {
    if($this->row == null || $this->col == null) return '';
    $all_letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $rowLetter = substr($all_letters, $this->row - 1, 1);
    return $rowLetter.$this->col;
  }
  /**
   * @param StigmerianToken $other
   * @return bool
   */
  public function isAdjacentToken($other)
  {
    if(!isset($other)) return false;
    return $this->isAdjacentCoord($other->row, $other->col);
  }

  /**
   * @param int $row
   * @param int $column
   * @return bool
   */
  public function isAdjacentCoord($row, $column)
  {
    //KEEP 4 DIRECT NEIGHBORS (no diagonals) among the 9 
    return $this->row == $row - 1 && $this->col == $column 
        || $this->row == $row + 1 && $this->col == $column 
        || $this->row == $row && $this->col == $column + 1
        || $this->row == $row && $this->col == $column - 1;
  }

  /**
   * @param StigmerianToken $other
   * @return bool
   */
  public function isDiagonalAdjacentToken($other)
  {
    if(!isset($other)) return false;
    return $this->isDiagonalAdjacentCoord($other->row, $other->col);
  }

  /**
   * @param int $row
   * @param int $column
   * @return bool
   */
  public function isDiagonalAdjacentCoord($row, $column)
  {
    //KEEP 4 diagonals NEIGHBORS among the 8
    return $this->row == $row - 1 && $this->col == $column -1
        || $this->row == $row + 1 && $this->col == $column +1
        || $this->row == $row - 1 && $this->col == $column + 1
        || $this->row == $row + 1 && $this->col == $column - 1;
  }
  /**
   * @param int $row
   * @param int $column
   * @return bool
   */
  public static function isCoordOutOfGrid($row, $column)
  {
    return GridUtils::isCoordOutOfGrid($row,$column);
  }
  /**
   * @return bool
   */
  public function isOutOfGrid()
  {
    return self::isCoordOutOfGrid($this->row, $this->col);
  }

  /**
   * @param Player $player
   * @param int $row
   * @param int $column
   * @param int $actionCost
   * @param bool $sendNotif (default false) : tells if we want to send a notif for this move
   */
  public function moveToPlayerBoard($player,$row,$column,$actionCost, $sendNotif = true)
  {
    $fromBoard = false;
    $fromDeck = false;
    if($this->getLocation() == TOKEN_LOCATION_PLAYER_BOARD ){
      $fromBoard = true;
      $fromCoord = $this->getCoordName();
    } else if( str_starts_with($this->getLocation(),TOKEN_LOCATION_PLAYER_DECK) ){
      $fromDeck = true;
    }
    else if($this->getLocation() == TOKEN_LOCATION_PLAYER_RECRUIT ){
      if(TOKEN_STIG_YELLOW == $this->getType()){
        $player->addTieBreakerPoints(-1);
      }
    }
    $this->setLocation(TOKEN_LOCATION_PLAYER_BOARD);
    $this->setPId($player->getId());
    $this->setCol($column);
    $this->setRow($row);

    if($fromBoard){
      if($sendNotif) Notifications::moveOnPlayerBoard($player, $this,$fromCoord,$this->getCoordName(),$actionCost);
    }
    else {
      Stats::inc("tokens_board",$player->getId());
      if($fromDeck){
        if($sendNotif) Notifications::moveFromDeckToPlayerBoard($player, $this,$actionCost);
      }
      else if($sendNotif) Notifications::moveToPlayerBoard($player, $this,$actionCost);
    }

    $this->checkAndBecomesPollen($player);
  }
  
  /**
   * @param Player $player
   * @param int $row
   * @param int $column
   * @param int $actionCost
   */
  public function moveToCentralBoard($player,$row,$column,$actionCost)
  {
    $fromBoard = false;
    if($this->getLocation() == TOKEN_LOCATION_CENTRAL_BOARD ){
      $fromBoard = true;
      $fromCoord = $this->getCoordName();
    }
    else if($this->getLocation() == TOKEN_LOCATION_PLAYER_RECRUIT ){
      if(TOKEN_STIG_YELLOW == $this->getType()){
        $player->addTieBreakerPoints(-1);
      }
    }
    $this->setLocation(TOKEN_LOCATION_CENTRAL_BOARD);
    $this->setPId(null);
    $this->setCol($column);
    $this->setRow($row);

    if($fromBoard){
      Notifications::moveOnCentralBoard($player,$this,$fromCoord,$this->getCoordName(),$actionCost);
    }
    else {
      Notifications::moveToCentralBoard($player,$this,$actionCost);
    }
  }
  
  /**
   * @param Player $player
   * @param int $actionCost
   */
  public function moveToRecruitZone($player,$actionCost)
  {
    $fromBoard = false;
    if($this->getLocation() == TOKEN_LOCATION_PLAYER_BOARD ){
      $fromBoard = true;
      $fromCoord = $this->getCoordName();
    }
    $this->setLocation(TOKEN_LOCATION_PLAYER_RECRUIT);
    $this->setPId($player->getId());
    $this->setCol(null);
    $this->setRow(null);

    Stats::inc("tokens_recruit",$player->getId(),1);
    if($fromBoard){
      Stats::inc("tokens_board",$player->getId(),-1);
      Notifications::moveBackToRecruit($player, $this,$fromCoord,$actionCost);
      if(TOKEN_STIG_YELLOW == $this->getType()){
        $player->addTieBreakerPoints(1);
      }
    }
    /* TODO if neeeded
    else {
      Notifications::moveToRecruitZone($player, $this,$actionCost);
    }*/
  }
  
  /**
   * @param Player $player
   * @param int $actionCost
   */
  public function moveToRecruitZoneCentral($player,$actionCost)
  {
    if($this->getLocation() == TOKEN_LOCATION_PLAYER_RECRUIT ){
      if(TOKEN_STIG_YELLOW == $this->getType()){
        $player->addTieBreakerPoints(-1);
      }
    }
    $fromCoord = $this->getCoordName();
    $this->setLocation(TOKEN_LOCATION_CENTRAL_RECRUIT);
    $this->setPId(null);
    $this->setCol(null);
    $this->setRow(null);
    Notifications::moveToCentralRecruit($player, $this,$fromCoord,$actionCost);
  }

  /**
   * @param Player $playerCurrent
   * @param Player $playerDestination
   */
  public function moveToPlayerBag($playerCurrent,$playerDestination)
  {
    $fromBoard = false;
    if($this->getLocation() == TOKEN_LOCATION_PLAYER_BOARD ){
      $fromBoard = true;
      $fromPlayer = $this->getPId();
    }
    else if($this->getLocation() == TOKEN_LOCATION_CENTRAL_BOARD ){

    }
    $this->setLocation(TOKEN_LOCATION_PLAYER_DECK.$playerDestination->getId());
    $this->setPId($playerDestination->getId());
    $this->setCol(null);
    $this->setRow(null);

    if($fromBoard){
      Stats::inc("tokens_board",$fromPlayer,-1);
    }
    Notifications::putTokenInBag($playerCurrent,$this,$playerDestination);
  }
  
  /**
   * Action of mxiing colors
   * @param StigmerianToken $other
   * @param Player $player
   * @param int $actionCost
   * @return bool true if colors are modified
   */
  public function mix($other,$player,$actionCost)
  {
    switch($this->type){
      //--------------------
      case TOKEN_STIG_RED:
        switch($other->type){
          case TOKEN_STIG_BLUE:
            $newColor = TOKEN_STIG_VIOLET;
            break;
          case TOKEN_STIG_YELLOW:
            $newColor = TOKEN_STIG_ORANGE;
            break;
        }
        break;
      //--------------------
      case TOKEN_STIG_BLUE:
        switch($other->type){
          case TOKEN_STIG_RED:
            $newColor = TOKEN_STIG_VIOLET;
            break;
          case TOKEN_STIG_YELLOW:
            $newColor = TOKEN_STIG_GREEN;
            break;
        }
        break;
      //--------------------
      case TOKEN_STIG_YELLOW:
        switch($other->type){
          case TOKEN_STIG_RED:
            $newColor = TOKEN_STIG_ORANGE;
            break;
          case TOKEN_STIG_BLUE:
            $newColor = TOKEN_STIG_GREEN;
            break;
        }
        break;
      //--------------------
    }
    //Finally change color
    if(isset($newColor)){
      $this->setType($newColor);
      $other->setType($newColor);
      Notifications::spMixing($player,$this,$other,$actionCost);
      $this->checkAndBecomesPollen($player);
      $other->checkAndBecomesPollen($player);
      return true;
    }
    return false;
  }
  
  /**
   * Check objective and becomes pollen if OK
   * @param Player $player
   */
  public function checkAndBecomesPollen($player)
  {
    //NO pollen on central board
    if($this->getPId() == null) return;
    if(Schemas::matchCurrentSchema($this)){
      $this->becomesPollen($player);
    }
  }
  /**
   * @param Player $player
   */
  public function becomesPollen($player)
  {
    $newType = TOKEN_POLLENS[$this->getType()];
    $this->setType($newType);
    Stats::inc("pollens_board",$this->getPId());
    Notifications::newPollen($player, $this);
  }
  
  /**
   * @return bool true if this token is on pollen side
   */
  public function isPollen()
  {
    if( array_search($this->getType(),TOKEN_POLLENS) === FALSE){
      return false;
    }
    return true;
  }

  /**
   * @return TokenCoord $coord equivalent datas
   */
  public function asCoord()
  {
    $coord = new TokenCoord($this->type, $this->row,$this->col );
    return $coord;
  }
  
  /**
   * @param TokenCoord $coord
   */
  public function updateCoord($coord)
  {
    if($this->col != $coord->col ) $this->setCol($coord->col);
    if($this->row != $coord->row ) $this->setRow($coord->row);
  }
  
  /**
   * @param TokenCoord $coord
   * @return bool true if all attributes are equivalent to the param
   */
  public function matchesCoord($coord)
  {
    if($this->col != $coord->col ) return false;
    if($this->row != $coord->row ) return false;
    if($this->type != $coord->type ) return false;
    return true;
  }


  public static function getTypeName($type)
  {
    switch($type){
      case TOKEN_STIG_BLUE:
      case TOKEN_POLLEN_BLUE:
        return Game::get()->translate("blue");
      case TOKEN_STIG_YELLOW:
      case TOKEN_POLLEN_YELLOW:
        return Game::get()->translate("yellow");
      case TOKEN_STIG_RED:
      case TOKEN_POLLEN_RED:
        return Game::get()->translate("red");
      case TOKEN_STIG_ORANGE:
      case TOKEN_POLLEN_ORANGE:
        return Game::get()->translate("orange");
      case TOKEN_STIG_GREEN:
      case TOKEN_POLLEN_GREEN:
        return Game::get()->translate("green");
      case TOKEN_STIG_VIOLET:
      case TOKEN_POLLEN_VIOLET:
        return Game::get()->translate("violet");
      case TOKEN_STIG_BROWN:
      case TOKEN_POLLEN_BROWN:
        return Game::get()->translate("brown");
      case TOKEN_STIG_WHITE:
      case TOKEN_POLLEN_WHITE:
        return Game::get()->translate("white");
      case TOKEN_STIG_BLACK:
      case TOKEN_POLLEN_BLACK:
        return Game::get()->translate("black");
      default: 
        return "";
    }
  }
}
