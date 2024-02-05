<?php

namespace STIG\Managers;

use STIG\Core\Game;
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
      ->map(function ($token) {
        return $token->getUiData();
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
  
  /**
   * @return StigmerianToken if found at that location, null otherwise
   */
  public static function findOnPersonalBoard($playerId,$row, $column)
  { 
    Game::get()->trace("findOnPersonalBoard($playerId,$row, $column)");
    return self::DB()
      ->where(static::$prefix . 'location', TOKEN_LOCATION_PLAYER_BOARD)
      ->wherePlayer($playerId)
      ->where('y', $row)
      ->where('x', $column)
      ->getSingle();
  }
  
  /**
   * @return Collection of StigmerianToken
   */
  public static function listAdjacentTokens($playerId,$row, $column)
  { 
    Game::get()->trace("listAdjacentTokens($playerId,$row, $column)");
    return self::DB()
      ->where(static::$prefix . 'location', TOKEN_LOCATION_PLAYER_BOARD)
      ->wherePlayer($playerId)
      ->whereIn('y', [$row - 1,  $row, $row + 1] )
      ->whereIn('x', [$column - 1, $column, $column + 1] )
      /*->orWhere()
      ->where(static::$prefix . 'location', TOKEN_LOCATION_PLAYER_BOARD)
      ->wherePlayer($playerId)->whereIn('y', [$row - 1] )->whereIn('x', [$column - 1] )
      ->orWhere()
      ->where(static::$prefix . 'location', TOKEN_LOCATION_PLAYER_BOARD)
      ->wherePlayer($playerId)->whereIn('y', [$row + 1] )->whereIn('x', [$column + 1] )
      ->orWhere()
      ->where(static::$prefix . 'location', TOKEN_LOCATION_PLAYER_BOARD)
      ->wherePlayer($playerId)->whereIn('y', [$row + 1] )->whereIn('x', [$column - 1] )
      */
      ->get()
      ->filter( function ($token) use ($row, $column) {
        //Game::get()->trace("filter listAdjacentTokens($row, $column) for $token->row, $token->col");
        //KEEP 4 DIRECT NEIGHBORS (no diagonals) among the 9 
        return $token->row == $row - 1 && $token->col == $column 
            || $token->row == $row + 1 && $token->col == $column 
            || $token->row == $row  && $token->col == $column + 1
            || $token->row == $row  && $token->col == $column - 1;
        }
      );
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
