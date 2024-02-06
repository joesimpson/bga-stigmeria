<?php

namespace STIG\Models;

use STIG\Core\Notifications;

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
  ];
  
  protected $staticAttributes = [
    ['type', 'int'],
    //Manage token face or state is enough ?
    //['pollen', 'bool'],
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

  public function moveToPlayerBoard($player,$row,$column)
  {
    $fromBoard = false;
    if($this->getLocation() == TOKEN_LOCATION_PLAYER_BOARD ){
      $fromBoard = true;
      $fromCoord = $this->getCoordName();
    }
    $this->setLocation(TOKEN_LOCATION_PLAYER_BOARD);
    $this->setPId($player->getId());
    $this->setCol($column);
    $this->setRow($row);

    if($fromBoard){
      Notifications::moveOnPlayerBoard($player, $this,$fromCoord,$this->getCoordName());
    }
    else {
      Notifications::moveToPlayerBoard($player, $this);
    }

    //TODO JSA Check if right positioned => becomes pollen
  }
  
}
