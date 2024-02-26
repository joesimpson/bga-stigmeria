<?php

namespace STIG\Models;

/*
 * DiceFace: all utility functions concerning a die/dice roll (12 faces in this game)
 */

class DiceFace implements \JsonSerializable
{
  /**
   * Face type : 12 unique faces
   */
  public int $type;

  public function __construct($type)
  {
    $this->type = $type;
  }

  /**
   * @return string wind direction
   */
  public function getWindDir()
  {
    switch($this->type){
      case NORTH_RED:
      case NORTH_BROWN:
        return WIND_DIR_NORTH;
      case SOUTH_BLUE:
      case SOUTH_GREEN:
        return WIND_DIR_SOUTH;
      case EAST_WHITE:
      case EAST_YELLOW:
        return WIND_DIR_EAST;
      case WEST_ORANGE:
      case WEST_VIOLET:
        return WIND_DIR_WEST;
      case X_RED:
      case X_BLUE:
      case X_YELLOW:
        //return WIND_DIR_UNKNOWN;
      case BLACK_NIGHT:
        return null;
    }
    return null;
  }
  
  /**
   * @return bool true when the wind direction must be chosen by player
   */
  public function askPlayerChoice()
  {
    switch($this->type){ 
      case BLACK_NIGHT:
        return true;
    }
    return false;
  }
  
  /**
   * @return bool true when the wind direction may be left unchosen by player
   */
  public function askPlayerNoChoice()
  {
    switch($this->type){ 
      case X_RED:
      case X_BLUE:
      case X_YELLOW:
        return true;
    }
    return false;
  }
  /**
   * @return bool true when this die may be rerolled by player
   */
  public function askPlayerReroll()
  {
    switch($this->type){ 
      case X_RED:
      case X_BLUE:
      case X_YELLOW:
        return true;
    }
    return false;
  }

  /**
   */
  public function getUiData()
  {
    $data = $this->jsonSerialize();
    return $data;
  }
  /**
   * Return an array of attributes
   */
  public function jsonSerialize()
  {
    $data = [];
    $data['type'] = $this->type;
    return $data;
  }
 
}
