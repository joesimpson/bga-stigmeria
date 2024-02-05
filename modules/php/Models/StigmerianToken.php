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
    'row' => ['x', 'int'],
    'col' => ['y', 'int'],
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

  public function moveToPlayerBoard($player,$row,$column)
  {
    $this->setLocation(TOKEN_LOCATION_PLAYER_BOARD);
    $this->setPId($player->getId());
    $this->setCol($column);
    $this->setRow($row);

    Notifications::moveToPlayerBoard($player, $this);
  }

}
