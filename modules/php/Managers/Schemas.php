<?php

namespace STIG\Managers;

use STIG\Core\Game;
use STIG\Core\Globals;
use STIG\Exceptions\UnexpectedException;
use STIG\Helpers\Collection;
use STIG\Models\Schema;
use STIG\Models\StigmerianToken;
use STIG\Models\TokenCoord;

/* 
Class to manage all the Schema cards for this game 
*/
class Schemas
{ 

  /**
   * Static definition of every puzzles we want to add int the game,
   * 
   * When adding, keep cells ordered from top to bottom, and left to right, it could be used by the game to help match boards tokens
   */
  public static function getTypes()
  {

    return [
      OPTION_SCHEMA_1 => new Schema(1, OPTION_FLOWER_VERTIGHAINEUSE, OPTION_DIFFICULTY_1, [],[
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
      OPTION_SCHEMA_3 => new Schema(3, OPTION_FLOWER_VERTIGHAINEUSE, OPTION_DIFFICULTY_2, [],[
          new TokenCoord( TOKEN_POLLEN_RED,  2,3 ),
          new TokenCoord( TOKEN_POLLEN_RED,  3,2 ),
          new TokenCoord( TOKEN_POLLEN_GREEN,3,3 ),
          new TokenCoord( TOKEN_POLLEN_RED,  3,4 ),
          new TokenCoord( TOKEN_POLLEN_RED,  4,3 ),
          new TokenCoord( TOKEN_POLLEN_GREEN,4,7 ),
          new TokenCoord( TOKEN_POLLEN_GREEN,7,4 ),
          new TokenCoord( TOKEN_POLLEN_RED,  7,8 ),
          new TokenCoord( TOKEN_POLLEN_RED,  8,7 ),
          new TokenCoord( TOKEN_POLLEN_GREEN,8,8 ),
          new TokenCoord( TOKEN_POLLEN_RED,  8,9 ),
          new TokenCoord( TOKEN_POLLEN_RED,  9,8 ),
          
        ] ),
      OPTION_SCHEMA_6 => new Schema(6, OPTION_FLOWER_VERTIGHAINEUSE, OPTION_DIFFICULTY_3, [],[
          new TokenCoord( TOKEN_POLLEN_ORANGE,1,1 ),
          new TokenCoord( TOKEN_POLLEN_RED,   1,2 ),
          new TokenCoord( TOKEN_POLLEN_RED,   1,9 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,1,10 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,2,2 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,2,10 ),
          new TokenCoord( TOKEN_POLLEN_GREEN, 6,5 ),
          new TokenCoord( TOKEN_POLLEN_RED,   7,1 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,7,2 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,8,1 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,8,9 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,9,10 ),
        ] ),
      OPTION_SCHEMA_8 => new Schema(8, OPTION_FLOWER_MARONNE, OPTION_DIFFICULTY_1, [],[
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
        
      OPTION_SCHEMA_10 => new Schema(10, OPTION_FLOWER_MARONNE, OPTION_DIFFICULTY_2, [],[
          new TokenCoord( TOKEN_POLLEN_BROWN, 6,5 ),
          new TokenCoord( TOKEN_POLLEN_BROWN, 7,6 ),
          new TokenCoord( TOKEN_POLLEN_BROWN, 8,5 ),
        ] ),
        
      OPTION_SCHEMA_11 => new Schema(11, OPTION_FLOWER_MARONNE, OPTION_DIFFICULTY_3, [],[
          new TokenCoord( TOKEN_POLLEN_BROWN, 6,3 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,  6,5 ),
          new TokenCoord( TOKEN_POLLEN_RED,   6,7 ),
          new TokenCoord( TOKEN_POLLEN_RED,     7,4 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  7,6 ),
          new TokenCoord( TOKEN_POLLEN_BROWN,   7,8 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW, 8,3 ),
          new TokenCoord( TOKEN_POLLEN_BROWN,  8,5 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,   8,7 ),
        ] ),
      OPTION_SCHEMA_13 => new Schema(13, OPTION_FLOWER_SIFFLOCHAMP, OPTION_DIFFICULTY_1, [],[
          new TokenCoord( TOKEN_POLLEN_WHITE, 6,5 ),
          new TokenCoord( TOKEN_POLLEN_WHITE, 6,6 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,7,4 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,7,7 ),
          new TokenCoord( TOKEN_POLLEN_BLACK, 8,3 ),
          new TokenCoord( TOKEN_POLLEN_RED,   8,5 ),
          new TokenCoord( TOKEN_POLLEN_RED,   8,6 ),
          new TokenCoord( TOKEN_POLLEN_BLACK, 8,8 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,  9,4 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,  9,7 ),
          
        ] ),
      OPTION_SCHEMA_14 => new Schema(14, OPTION_FLOWER_SIFFLOCHAMP, OPTION_DIFFICULTY_2, [
          new TokenCoord( TOKEN_STIG_BLUE,    1,5 ),
          new TokenCoord( TOKEN_STIG_RED,     1,6 ),
          new TokenCoord( TOKEN_STIG_BLUE,    1,7 ),
          new TokenCoord( TOKEN_STIG_YELLOW,  2,6 ),
          new TokenCoord( TOKEN_STIG_YELLOW,  3,6 ),
          new TokenCoord( TOKEN_STIG_BLACK,   5,1 ),
          new TokenCoord( TOKEN_STIG_BLACK,   6,1 ),
          new TokenCoord( TOKEN_STIG_BLACK,   7,1 ),
          new TokenCoord( TOKEN_STIG_BLACK,   8,10 ),
          new TokenCoord( TOKEN_STIG_BLACK,   9,9 ),
          new TokenCoord( TOKEN_STIG_BLACK,   9,10 ),
        ],[
          new TokenCoord( TOKEN_POLLEN_YELLOW,5,5 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,5,6 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,5,7 ),
          new TokenCoord( TOKEN_POLLEN_RED,   6,5 ),
          new TokenCoord( TOKEN_POLLEN_BLACK, 6,6 ),
          new TokenCoord( TOKEN_POLLEN_RED,   6,7 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,  7,5 ),
          new TokenCoord( TOKEN_POLLEN_WHITE, 7,6 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,  7,7 ),
          new TokenCoord( TOKEN_POLLEN_BLACK, 8,1 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,  8,5 ),
          new TokenCoord( TOKEN_POLLEN_BLACK, 8,6 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,  8,7 ),
          new TokenCoord( TOKEN_POLLEN_WHITE, 9,1 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,  9,5 ),
          new TokenCoord( TOKEN_POLLEN_WHITE, 9,6 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,  9,7 ),
          new TokenCoord( TOKEN_POLLEN_BLACK,10,1 ),
          new TokenCoord( TOKEN_POLLEN_RED,  10,5 ),
          new TokenCoord( TOKEN_POLLEN_RED,  10,6 ),
          new TokenCoord( TOKEN_POLLEN_RED,  10,7 ),
          new TokenCoord( TOKEN_POLLEN_WHITE,10,10 ),
        ] ),
      OPTION_SCHEMA_18 => new Schema(18, OPTION_FLOWER_SIFFLOCHAMP, OPTION_DIFFICULTY_3, [],[
          new TokenCoord( TOKEN_POLLEN_BLACK, 9,1 ),
          new TokenCoord( TOKEN_POLLEN_BLACK, 9,2 ),  
          new TokenCoord( TOKEN_POLLEN_BLUE,  9,3 ),
          new TokenCoord( TOKEN_POLLEN_WHITE, 9,4 ),
          new TokenCoord( TOKEN_POLLEN_WHITE, 9,5 ),
          new TokenCoord( TOKEN_POLLEN_WHITE, 9,6 ),
          new TokenCoord( TOKEN_POLLEN_WHITE, 9,7 ),
          new TokenCoord( TOKEN_POLLEN_RED,   9,8 ),
          new TokenCoord( TOKEN_POLLEN_BLACK, 9,9 ),
          new TokenCoord( TOKEN_POLLEN_BLACK, 9,10 ),
        ] ),
        
      OPTION_SCHEMA_20 => new Schema(20, OPTION_FLOWER_DENTDINE, OPTION_DIFFICULTY_1, [
          new TokenCoord( TOKEN_STIG_BROWN,    1,5 ),
          new TokenCoord( TOKEN_STIG_BROWN,    1,6 ),
          new TokenCoord( TOKEN_STIG_BLACK,    2,4 ),
          new TokenCoord( TOKEN_STIG_BLACK,    2,7 ),
          new TokenCoord( TOKEN_STIG_WHITE,    3,3 ),
          new TokenCoord( TOKEN_STIG_WHITE,    3,8 ),

        ],[
          new TokenCoord( TOKEN_POLLEN_YELLOW,  2,3 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  2,8 ),
          new TokenCoord( TOKEN_POLLEN_RED,     3,4 ),
          new TokenCoord( TOKEN_POLLEN_RED,     3,7 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    4,5 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    4,6 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  6,1 ),
          new TokenCoord( TOKEN_POLLEN_RED,     6,2 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    6,3 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    6,8 ),
          new TokenCoord( TOKEN_POLLEN_RED,     6,9 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  6,10 ),
          new TokenCoord( TOKEN_POLLEN_WHITE,   8,3 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  8,4 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  8,7 ),
          new TokenCoord( TOKEN_POLLEN_WHITE,   8,8 ),
          new TokenCoord( TOKEN_POLLEN_BLACK,   9,2 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    9,5 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    9,6 ),
          new TokenCoord( TOKEN_POLLEN_BLACK,   9,9 ),
          new TokenCoord( TOKEN_POLLEN_BROWN,   10,1 ),
          new TokenCoord( TOKEN_POLLEN_RED,     10,4 ),
          new TokenCoord( TOKEN_POLLEN_RED,     10,7 ),
          new TokenCoord( TOKEN_POLLEN_BROWN,   10,10 ),
        ] ),
        
      OPTION_SCHEMA_21 => new Schema(21, OPTION_FLOWER_DENTDINE, OPTION_DIFFICULTY_2, [],[
          new TokenCoord( TOKEN_POLLEN_YELLOW,  1,1 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    1,3 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    1,8 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  1,10 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  2,2 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    2,4 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    2,7 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  2,10),
          new TokenCoord( TOKEN_POLLEN_RED,     4,4 ),
          new TokenCoord( TOKEN_POLLEN_RED,     4,7 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  5,5 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  5,6 ),
          new TokenCoord( TOKEN_POLLEN_RED,     6,5 ),
          new TokenCoord( TOKEN_POLLEN_RED,     6,6 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  7,4 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  7,7 ),
          new TokenCoord( TOKEN_POLLEN_RED,     9,2 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    9,4 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    9,7 ),
          new TokenCoord( TOKEN_POLLEN_RED,     9,9 ),
          new TokenCoord( TOKEN_POLLEN_RED,     10,1 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    10,3 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    10,8 ),
          new TokenCoord( TOKEN_POLLEN_RED,     10,10 ),
        ] ),
        
      OPTION_SCHEMA_23 => new Schema(23, OPTION_FLOWER_DENTDINE, OPTION_DIFFICULTY_3, [
          new TokenCoord( TOKEN_STIG_RED,       1,1 ),
          new TokenCoord( TOKEN_STIG_BLUE,      1,10 ),
          new TokenCoord( TOKEN_STIG_ORANGE,    5,4 ),
          new TokenCoord( TOKEN_STIG_BLACK,     5,5 ),
          new TokenCoord( TOKEN_STIG_BLACK,     5,6 ),
          new TokenCoord( TOKEN_STIG_ORANGE,    5,7 ),
          new TokenCoord( TOKEN_STIG_BROWN,     6,4 ),
          new TokenCoord( TOKEN_STIG_YELLOW,    6,5 ),
          new TokenCoord( TOKEN_STIG_YELLOW,    6,6 ),
          new TokenCoord( TOKEN_STIG_BROWN,     6,7 ),
          new TokenCoord( TOKEN_STIG_GREEN,     7,4 ),
          new TokenCoord( TOKEN_STIG_YELLOW,    7,5 ),
          new TokenCoord( TOKEN_STIG_YELLOW,    7,6 ),
          new TokenCoord( TOKEN_STIG_GREEN,     7,7 ),
          new TokenCoord( TOKEN_STIG_VIOLET,    8,4 ),
          new TokenCoord( TOKEN_STIG_WHITE,     8,5 ),
          new TokenCoord( TOKEN_STIG_WHITE,     8,6 ),
          new TokenCoord( TOKEN_STIG_VIOLET,    8,7 ),

        ],[
          new TokenCoord( TOKEN_POLLEN_VIOLET,  5,2 ),
          new TokenCoord( TOKEN_POLLEN_WHITE,   5,3 ),
          new TokenCoord( TOKEN_POLLEN_WHITE,   5,8 ),
          new TokenCoord( TOKEN_POLLEN_VIOLET,  5,9 ),
          new TokenCoord( TOKEN_POLLEN_BLACK,   6,2 ),
          new TokenCoord( TOKEN_POLLEN_GREEN,   6,3 ),
          new TokenCoord( TOKEN_POLLEN_GREEN,   6,8 ),
          new TokenCoord( TOKEN_POLLEN_BLACK,   6,9 ),
          new TokenCoord( TOKEN_POLLEN_BROWN,   8,2 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,  8,3 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,  8,8 ),
          new TokenCoord( TOKEN_POLLEN_BROWN,   8,9 ),
          new TokenCoord( TOKEN_POLLEN_RED,    10,1 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,   10,2 ),
          new TokenCoord( TOKEN_POLLEN_RED,    10,3 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,   10,4 ),
          new TokenCoord( TOKEN_POLLEN_RED,    10,5 ),
          new TokenCoord( TOKEN_POLLEN_RED,    10,6 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,   10,7 ),
          new TokenCoord( TOKEN_POLLEN_RED,    10,8 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,   10,9 ),
          new TokenCoord( TOKEN_POLLEN_RED,    10,10),
        ] ),
      OPTION_SCHEMA_24 => new Schema(24, OPTION_FLOWER_DENTDINE, OPTION_DIFFICULTY_3, [
          new TokenCoord( TOKEN_STIG_BLUE,        1,2 ), 
          new TokenCoord( TOKEN_STIG_WHITE,       1,10 ), 
          new TokenCoord( TOKEN_STIG_RED,         2,1 ), 
          new TokenCoord( TOKEN_STIG_ORANGE,      2,2 ), 
          new TokenCoord( TOKEN_STIG_BLUE,        2,3 ), 
          new TokenCoord( TOKEN_STIG_BLUE,        2,4 ), 
          new TokenCoord( TOKEN_STIG_BLUE,        2,5 ), 
          new TokenCoord( TOKEN_STIG_GREEN,       2,6 ), 
          new TokenCoord( TOKEN_STIG_GREEN,       2,7 ), 
          new TokenCoord( TOKEN_STIG_GREEN,       2,8 ), 
          new TokenCoord( TOKEN_STIG_GREEN,       2,9 ), 
          new TokenCoord( TOKEN_STIG_GREEN,       2,10 ), 
          new TokenCoord( TOKEN_STIG_WHITE,       3,1 ), 
          new TokenCoord( TOKEN_STIG_VIOLET,      3,2 ), 
          new TokenCoord( TOKEN_STIG_ORANGE,      3,3 ),  
          new TokenCoord( TOKEN_STIG_BLACK,       3,5 ), 
          new TokenCoord( TOKEN_STIG_BLACK,       3,6 ), 
          new TokenCoord( TOKEN_STIG_BLACK,       3,10 ), 
          new TokenCoord( TOKEN_STIG_ORANGE,      4,2 ),  
          new TokenCoord( TOKEN_STIG_VIOLET,      4,3 ), 
          new TokenCoord( TOKEN_STIG_BLACK,       4,6 ), 
          new TokenCoord( TOKEN_STIG_YELLOW,      4,7 ), 
          new TokenCoord( TOKEN_STIG_ORANGE,      5,2 ),  
          new TokenCoord( TOKEN_STIG_VIOLET,      5,3 ), 
          new TokenCoord( TOKEN_STIG_WHITE,       5,6 ), 
          new TokenCoord( TOKEN_STIG_YELLOW,      5,7 ), 
          new TokenCoord( TOKEN_STIG_ORANGE,      6,2 ),  
          new TokenCoord( TOKEN_STIG_VIOLET,      6,3 ), 
          new TokenCoord( TOKEN_STIG_WHITE,       6,6 ), 
          new TokenCoord( TOKEN_STIG_RED,         6,7 ), 
          new TokenCoord( TOKEN_STIG_VIOLET,      7,3 ), 
          new TokenCoord( TOKEN_STIG_RED,         7,7 ), 
          new TokenCoord( TOKEN_STIG_RED,         8,7 ), 
          new TokenCoord( TOKEN_STIG_BLACK,       8,9 ), 
          new TokenCoord( TOKEN_STIG_BROWN,       9,1 ), 
          new TokenCoord( TOKEN_STIG_BROWN,       9,2 ), 
          new TokenCoord( TOKEN_STIG_BROWN,       9,3 ), 
          new TokenCoord( TOKEN_STIG_BROWN,       9,4 ), 
          new TokenCoord( TOKEN_STIG_BROWN,       9,5 ), 
          new TokenCoord( TOKEN_STIG_YELLOW,      9,6 ), 
          new TokenCoord( TOKEN_STIG_YELLOW,      9,7 ), 
          new TokenCoord( TOKEN_STIG_WHITE,       9,9 ), 
        ],[
          new TokenCoord( TOKEN_POLLEN_BLACK,   1,9 ), 
          new TokenCoord( TOKEN_POLLEN_BLACK,   2,9 ),  
          new TokenCoord( TOKEN_POLLEN_BLACK,   2,10 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    3,2 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    3,3 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    3,4 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    3,5 ),
          new TokenCoord( TOKEN_POLLEN_GREEN,   3,6 ),
          new TokenCoord( TOKEN_POLLEN_GREEN,   3,7 ),
          new TokenCoord( TOKEN_POLLEN_GREEN,   3,8 ),
          new TokenCoord( TOKEN_POLLEN_GREEN,   3,9 ),
          new TokenCoord( TOKEN_POLLEN_GREEN,   3,10 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,  4,2 ),
          new TokenCoord( TOKEN_POLLEN_BLACK,   4,6 ),
          new TokenCoord( TOKEN_POLLEN_WHITE,   5,1 ),
          new TokenCoord( TOKEN_POLLEN_VIOLET,  5,2 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,  5,4 ),
          new TokenCoord( TOKEN_POLLEN_BLACK,   5,6 ),
          new TokenCoord( TOKEN_POLLEN_RED,     5,8 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,  6,2 ),
          new TokenCoord( TOKEN_POLLEN_VIOLET,  6,4 ),
          new TokenCoord( TOKEN_POLLEN_WHITE,   6,6 ),
          new TokenCoord( TOKEN_POLLEN_RED,     6,8 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,  7,2 ),
          new TokenCoord( TOKEN_POLLEN_VIOLET,  7,4 ),
          new TokenCoord( TOKEN_POLLEN_WHITE,   7,6 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  7,7 ),
          new TokenCoord( TOKEN_POLLEN_RED,     7,8 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,  8,2 ),
          new TokenCoord( TOKEN_POLLEN_VIOLET,  8,4 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  8,8 ),
          new TokenCoord( TOKEN_POLLEN_VIOLET,  9,4 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  9,8 ),
          new TokenCoord( TOKEN_POLLEN_BROWN,   10,1 ),
          new TokenCoord( TOKEN_POLLEN_BROWN,   10,2 ),
          new TokenCoord( TOKEN_POLLEN_BROWN,   10,3 ),
          new TokenCoord( TOKEN_POLLEN_BROWN,   10,4 ),
          new TokenCoord( TOKEN_POLLEN_BROWN,   10,5 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  10,6 ),
          new TokenCoord( TOKEN_POLLEN_RED,     10,7 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  10,8 ),
          new TokenCoord( TOKEN_POLLEN_WHITE,   10,10 ),
        ] ),
        
      OPTION_SCHEMA_25 => new Schema(25, OPTION_FLOWER_INSPIRACTRICE, OPTION_DIFFICULTY_1, [],[
          new TokenCoord( TOKEN_POLLEN_VIOLET,   1,1 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,   1,4 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,   1,7 ),
          new TokenCoord( TOKEN_POLLEN_VIOLET,   1,10 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,     5,5 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,     5,6 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,   6,5 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,   6,6 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,   7,4 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,   7,7 ),
          new TokenCoord( TOKEN_POLLEN_RED,      8,3 ),
          new TokenCoord( TOKEN_POLLEN_RED,      8,8 ),
          new TokenCoord( TOKEN_POLLEN_GREEN,    10,1 ),
          new TokenCoord( TOKEN_POLLEN_RED,      10,4 ),
          new TokenCoord( TOKEN_POLLEN_RED,      10,7 ),
          new TokenCoord( TOKEN_POLLEN_GREEN,    10,10 ),
        ] ),
      OPTION_SCHEMA_26 => new Schema(26, OPTION_FLOWER_INSPIRACTRICE, OPTION_DIFFICULTY_2, [],[
          new TokenCoord( TOKEN_POLLEN_BLUE,    4,4 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    4,5 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    4,6 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    4,7 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    4,8 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  5,4 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  5,5 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  5,6 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  5,7 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  5,8 ),
          new TokenCoord( TOKEN_POLLEN_RED,     6,4 ),
          new TokenCoord( TOKEN_POLLEN_RED,     6,5 ),
          new TokenCoord( TOKEN_POLLEN_RED,     6,6 ),
          new TokenCoord( TOKEN_POLLEN_RED,     6,7 ),
          new TokenCoord( TOKEN_POLLEN_RED,     6,8 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    7,4 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    7,5 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    7,6 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    7,7 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    7,8 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  8,4 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  8,5 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  8,6 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  8,7 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  8,8 ),
          new TokenCoord( TOKEN_POLLEN_RED,     9,4 ),
          new TokenCoord( TOKEN_POLLEN_RED,     9,5 ),
          new TokenCoord( TOKEN_POLLEN_RED,     9,6 ),
          new TokenCoord( TOKEN_POLLEN_RED,     9,7 ),
          new TokenCoord( TOKEN_POLLEN_RED,     9,8 ),
        ] ),
      OPTION_SCHEMA_28 => new Schema(28, OPTION_FLOWER_INSPIRACTRICE, OPTION_DIFFICULTY_3, [],[
          new TokenCoord( TOKEN_POLLEN_GREEN,   1,4 ),
          new TokenCoord( TOKEN_POLLEN_RED,     1,5 ),
          new TokenCoord( TOKEN_POLLEN_BROWN,   1,6 ),
          new TokenCoord( TOKEN_POLLEN_VIOLET,  1,7 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,  1,8 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  10,4 ),
          new TokenCoord( TOKEN_POLLEN_WHITE,   10,5 ),
          new TokenCoord( TOKEN_POLLEN_BLACK,   10,6 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    10,7 ),
        ] ),
        
      OPTION_SCHEMA_33 => new Schema(33, OPTION_FLOWER_COMPETITIVE, OPTION_DIFFICULTY_1, [],[
          // Reversi LOOKALIKE
          new TokenCoord( TOKEN_POLLEN_WHITE, 5,5 ),
          new TokenCoord( TOKEN_POLLEN_BLACK, 5,6 ),
          new TokenCoord( TOKEN_POLLEN_BLACK, 6,5 ),
          new TokenCoord( TOKEN_POLLEN_WHITE, 6,6 ),
        ] ),
      OPTION_SCHEMA_36 => new Schema(36, OPTION_FLOWER_COMPETITIVE, OPTION_DIFFICULTY_2, [],[
          new TokenCoord( TOKEN_POLLEN_VIOLET,  4,5 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    4,6 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    5,4 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  5,5 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  5,6 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    5,7 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    6,3 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  6,4 ),
          new TokenCoord( TOKEN_POLLEN_RED,     6,5 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,  6,6 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  6,7 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    6,8 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    7,3 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  7,4 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,  7,5 ),
          new TokenCoord( TOKEN_POLLEN_RED,     7,6 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  7,7 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    7,8 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    8,4 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  8,5 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  8,6 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    8,7 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    9,5 ),
          new TokenCoord( TOKEN_POLLEN_VIOLET,  9,6 ),
        ] ),
      OPTION_SCHEMA_39 => new Schema(39, OPTION_FLOWER_COMPETITIVE, OPTION_DIFFICULTY_3, [],[
          new TokenCoord( TOKEN_POLLEN_GREEN,   5,4 ),
          new TokenCoord( TOKEN_POLLEN_VIOLET,  5,5 ),
          new TokenCoord( TOKEN_POLLEN_VIOLET,  5,6 ),
          new TokenCoord( TOKEN_POLLEN_GREEN,   5,7 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  6,4 ),
          new TokenCoord( TOKEN_POLLEN_WHITE,   6,5 ),
          new TokenCoord( TOKEN_POLLEN_WHITE,   6,6 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    6,7 ),
          new TokenCoord( TOKEN_POLLEN_BROWN,   7,4 ),
          new TokenCoord( TOKEN_POLLEN_RED,     7,5 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    7,6 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  7,7 ),
          new TokenCoord( TOKEN_POLLEN_BLACK,   8,5 ),
          new TokenCoord( TOKEN_POLLEN_BLACK,   8,6 ),
        ] ),
      OPTION_SCHEMA_45 => new Schema(45, OPTION_FLOWER_NO_LIMIT, OPTION_DIFFICULTY_4, [],[
          new TokenCoord( TOKEN_POLLEN_BLUE,    1,1 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    1,2 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    1,9 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    1,10 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    2,1 ),
          new TokenCoord( TOKEN_POLLEN_BLUE,    2,10 ),
          new TokenCoord( TOKEN_POLLEN_GREEN,   3,5 ),
          new TokenCoord( TOKEN_POLLEN_GREEN,   3,6 ),
          new TokenCoord( TOKEN_POLLEN_GREEN,   4,4 ),
          new TokenCoord( TOKEN_POLLEN_GREEN,   4,7 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,  5,4 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,  5,7 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,  6,5 ),
          new TokenCoord( TOKEN_POLLEN_ORANGE,  6,6 ),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  7,5),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  7,6),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  8,4),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  8,5),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  8,6),
          new TokenCoord( TOKEN_POLLEN_YELLOW,  8,7),
          new TokenCoord( TOKEN_POLLEN_RED,     9,3),
          new TokenCoord( TOKEN_POLLEN_RED,     9,4),
          new TokenCoord( TOKEN_POLLEN_RED,     9,5),
          new TokenCoord( TOKEN_POLLEN_RED,     9,6),
          new TokenCoord( TOKEN_POLLEN_RED,     9,7),
          new TokenCoord( TOKEN_POLLEN_RED,     9,8),
          new TokenCoord( TOKEN_POLLEN_RED,     10,2),
          new TokenCoord( TOKEN_POLLEN_RED,     10,3),
          new TokenCoord( TOKEN_POLLEN_RED,     10,4),
          new TokenCoord( TOKEN_POLLEN_RED,     10,5),
          new TokenCoord( TOKEN_POLLEN_RED,     10,6),
          new TokenCoord( TOKEN_POLLEN_RED,     10,7),
          new TokenCoord( TOKEN_POLLEN_RED,     10,8),
          new TokenCoord( TOKEN_POLLEN_RED,     10,9),
        ] ),
      //TODO JSA OTHER SCHEMAS
    ];
  }
  
  public static function getUiData()
  {
    $collection = new Collection(self::getTypes());
    //Send only current schema in normal/compet games, we will see later for challenge when we send the used schemas (new round ?)
    $ids = [Globals::getOptionSchema()];
    $collection = $collection->filter(function($schema) use ($ids){
      return in_array($schema->id,$ids);
    });
    return $collection->uiAssoc();
  }
  
  /**
   * @return Schema
   */
  public static function getCurrentSchema()
  {
    $optionSchema = Globals::getOptionSchema();
    if(!isset($optionSchema)) throw new UnexpectedException(1,"Missing schema $optionSchema!");
    $types = Schemas::getTypes();
    if(!array_key_exists($optionSchema,$types)) throw new UnexpectedException(1,"Missing schema $optionSchema!");
    return Schemas::getTypes()[$optionSchema];
  }

  /**
   * @param StigmerianToken $token
   * @return bool true if token is expected in ending current schema
   */
  public static function matchCurrentSchema($token)
  {
    if(! array_key_exists($token->getType(),TOKEN_POLLENS)) return false;
    $tokenFuturePollen =  TOKEN_POLLENS[$token->getType()];
    $schema = Schemas::getCurrentSchema();
    if(! isset($schema)) return false;
    $notfound = $schema->end->filter( function ($expected) use ($token, $tokenFuturePollen) {
        //Game::get()->trace("matchCurrentSchema() loop ".json_encode($expected->getUiData()));
        //Compare TokenCoord VS StigmerianToken
        return $expected->row == $token->row 
            && $expected->col == $token->col
            && $expected->type == $tokenFuturePollen;
        ;
      }
    )->isEmpty();
    //Game::get()->trace("matchCurrentSchema() notfound ? ".json_encode($notfound)." for ".json_encode($token->getUiData()));
    return !$notfound;
  }
}
