<?php

namespace STIG\Managers;

use STIG\Helpers\Collection;
use STIG\Models\DiceFace;

class DiceRoll
{ 

  /**
   * @param DieFace $dieFace
   * @return int the stgmerian color corresponding to this die face
   */
  public static function getStigmerianFromDie($dieFace){
    switch($dieFace){
      case NORTH_RED:
        return TOKEN_STIG_RED;
      case NORTH_BROWN:
        return TOKEN_STIG_BROWN;
      case SOUTH_GREEN:
        return TOKEN_STIG_GREEN;
      case SOUTH_BLUE:
        return TOKEN_STIG_BLUE;
      case EAST_YELLOW:
        return TOKEN_STIG_YELLOW;
      case EAST_WHITE:
        return TOKEN_STIG_WHITE;
      case WEST_VIOLET:
        return TOKEN_STIG_VIOLET;
      case WEST_ORANGE:
        return TOKEN_STIG_ORANGE;
      case X_RED:
        return TOKEN_STIG_RED;
      case X_BLUE:
        return TOKEN_STIG_BLUE;
      case X_YELLOW:
        return TOKEN_STIG_YELLOW;
      case BLACK_NIGHT:
        return TOKEN_STIG_BLACK;
    }
    return null;
  }
  
  /**
   * 
   * @return DieFace
   */
  public static function rollNew(){
    return new DiceFace(self::getAll()->rand());
  }
  /**
   * @return Collection of DieFace
   */
  public static function getAll(){
    
    return new Collection([
      NORTH_RED, NORTH_BROWN,
      SOUTH_GREEN, SOUTH_BLUE,
      EAST_YELLOW, EAST_WHITE,
      WEST_VIOLET, WEST_ORANGE,
      X_RED, X_BLUE, X_YELLOW,
      BLACK_NIGHT,
    ]);
  }
 
}
