<?php

namespace STIG\Managers;

use STIG\Core\Game;
use STIG\Helpers\Collection;
use STIG\Models\Schema;
use STIG\Models\TokenCoord;

/* 
Class to manage all the Schema cards for this game 
*/
class Schemas
{ 

  public static function getTypes()
  {

    return [
      1 => new Schema(1, OPTION_FLOWER_VERTIGHAINEUSE, 1, [],[
          new TokenCoord( TOKEN_POLLEN_BLUE,  2,6 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,3,5 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,  4,3 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,5,6 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,6,5 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,6,6 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,  6,8 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,7,5 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,  8,3 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,9,6 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,  10,5 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,  10,8 ),
          
        ] ),
      8 => new Schema(8, OPTION_FLOWER_MARONNE, 1, [],[
          new TokenCoord( TOKEN_POLLEN_YELLOW,3,5 ),
          new TokenCoord( TOKEN_POLLEN_RED,   4,6 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,  5,5 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,  5,7 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,6,4 ),
          new TokenCoord( TOKEN_POLLEN_BROWN, 6,6 ),
          new TokenCoord( TOKEN_POLLEN_RED,   6,8 ),
          new TokenCoord( TOKEN_POLLEN_RED,   7,4 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,  7,8 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,8,5 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,8,7 ),
          new TokenCoord( TOKEN_POLLEN_RED,   9,6 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,  10,5 ),
        ] ),
        //TODO JSA OTHER SCHEMAS
    ];
  }
  
  public static function getUiData()
  {
    $collection = new Collection(self::getTypes());
    return $collection->uiAssoc();
  }
  
}
