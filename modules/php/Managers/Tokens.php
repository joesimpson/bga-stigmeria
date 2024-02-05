<?php

namespace STIG\Managers;

use STIG\Models\StigmerianToken;

/* 
Class to manage all the tokens for this game 
*/
class Tokens extends \STIG\Helpers\Pieces
{
  protected static $table = 'token';
  protected static $prefix = 'token_';
  protected static $autoIncrement = true;
  protected static $autoremovePrefix = false;
  protected static $customFields = ['type', 'player_id','x', 'y'];

  protected static function cast($row)
  {
    /*
    $data = self::getTokenTypes()[$row['type']];
    */
    $data['type'] = $row['type'];
    return new StigmerianToken($row, $data);
  }

  public static function getUiData()
  {
    return self::getAll()
        //TODO JSA FILTER visible TOKENS (not in draw bags)
      ->map(function ($tile) {
        return $tile->getUiData();
      })
      ->toArray();
  }

  /* Creation of the tokens */
  public static function setupNewGame($players, $options)
  {
    $tokens = [];
    // Create the deck with 15 of each primary color to each player
    foreach ($players as $pId => $player) {
        foreach (STIG_PRIMARY_COLORS as $color) {
            $tokens[] = [
              'type' => $color,
              'location' => TOKEN_LOCATION_PLAYER_DECK.$pId,
              'player_id' => $pId,
              'nbr' => TOKEN_SETUP_NB,
            ];
          }
    }

    self::create($tokens);

    foreach ($players as $pId => $player) {
        self::shuffle(TOKEN_LOCATION_PLAYER_DECK.$pId);
        //Draw 1 to each recruit zone :
        self::pickForLocation(1,TOKEN_LOCATION_PLAYER_DECK.$pId, TOKEN_LOCATION_PLAYER_RECRUIT, TOKEN_STATE_STIGMERIAN);
    }
  }
 
  /*
  public function getTokenTypes()
  {
    $f = function ($t) {
      return [
        'pollen' => $t[0],
      ];
    };

    return [
      1 => $f([STIG_COLOR_1, false]),
    ];
  }
  */
}
