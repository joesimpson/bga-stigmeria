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
    $data = [];
    return new StigmerianToken($row, $data);
  }

  public static function getUiData()
  {
    return self::DB()
      //FILTER visible TOKENS (not in draw bags)
      ->whereNotLike(static::$prefix . 'location', [TOKEN_LOCATION_PLAYER_DECK.'%'])
      ->get()
      ->map(function ($token) {
        return $token->getUiData();
      })
      ->toArray();
  }

  public static function setupNewGame($players, $options)
  {
    
  }
  
  /**
   * @param Collection $players Player
   * @param Schema $schema 
   * @return Collection
   */
  public static function setupNewRound($players,$schema)
  {
    //DELETE ALL
    self::DB()->delete()->run();

    /* Creation of the tokens for the round */
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
      //Init starting schema :
      foreach ($schema->start as $token) {
        $tokens[] = [
          'type' => $token->type,
          'location' => TOKEN_LOCATION_PLAYER_BOARD,
          'player_id' => $pId,
          'y' => $token->row,
          'x' => $token->col,
          'nbr' => 1,
        ];
      }
    }

    self::create($tokens);

    foreach ($players as $pId => $player) {
        self::shuffle(TOKEN_LOCATION_PLAYER_DECK.$pId);
        //Draw 1 to each recruit zone :
        self::pickForLocation(1,TOKEN_LOCATION_PLAYER_DECK.$pId, TOKEN_LOCATION_PLAYER_RECRUIT, TOKEN_STATE_STIGMERIAN);
    }

    return self::getAll();
  }
  
  /**
   * 
   * @param int $playerId
   * @param int $row
   * @param int $column
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
   * SEARCH in memory, not in DB
   * @param array $tokens StigmerianToken
   * @param int $row
   * @param int $column
   * @return StigmerianToken if found at that location, null otherwise
   */
  public static function findTokenOnBoardWithCoord($boardTokens,$row,$column )
  { 
    Game::get()->trace("findTokenOnBoardWithCoord($row, $column)");
    return $boardTokens->filter( function ($token) use ($row, $column) {
        return $token->row == $row && $token->col ==$column;
      }
    )->first();
  }
  /**
   * @param int $playerId
   * @return Collection of StigmerianToken found at that location
   */
  public static function getAllOnPersonalBoard($playerId)
  { 
    Game::get()->trace("getAllOnPersonalBoard($playerId)");
    return self::DB()
      ->where(static::$prefix . 'location', TOKEN_LOCATION_PLAYER_BOARD)
      ->wherePlayer($playerId)
      ->get();
  }
  /**
   * @param int $playerId
   * @return Collection of StigmerianToken found at that location
   */
  public static function getAllRecruits($playerId)
  { 
    Game::get()->trace("getAllRecruits($playerId)");
    return self::DB()
      ->where(static::$prefix . 'location', TOKEN_LOCATION_PLAYER_RECRUIT)
      ->wherePlayer($playerId)
      ->get();
  }
   /**
   * @return Collection of StigmerianToken found at that location
   */
  public static function getAllCentralTokensToPlace()
  { 
    Game::get()->trace("getAllCentralTokensToPlace()");
    return self::DB()
      ->where(static::$prefix . 'location', TOKEN_LOCATION_CENTRAL_RECRUIT_TOPLACE)
      ->get();
  }
  /**
   * @return Collection of StigmerianToken found at that location
   */
  public static function getAllOnCentralBoard()
  { 
    Game::get()->trace("getAllOnCentralBoard()");
    return self::DB()
      ->where(static::$prefix . 'location', TOKEN_LOCATION_CENTRAL_BOARD)
      ->get();
  }
  /**
   * @param int $row
   * @param int $column
  * @return int nb of tokens on central board at these coordinates
  */
  public static function countOnCentralBoard($row, $column)
  { 
    //Game::get()->trace("countOnCentralBoard($row, $column)");
    return self::DB()
      ->where(static::$prefix . 'location', TOKEN_LOCATION_CENTRAL_BOARD)
      ->where('y', $row)
      ->where('x', $column)
      ->count();
  }
  
  /**
   * @param int $playerId
   * @param int $row
   * @param int $column
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
          return $token->isAdjacentCoord($row, $column);
        }
      );
  }
  
  /**
   * @param int $row
   * @param int $column
   * @return Collection of StigmerianToken
   */
  public static function listAdjacentTokensOnCentral($row, $column)
  { 
    Game::get()->trace("listAdjacentTokensOnCentral($row, $column)");
    return self::DB()
      ->where(static::$prefix . 'location', TOKEN_LOCATION_CENTRAL_BOARD)
      ->whereIn('y', [$row - 1,  $row, $row + 1] )
      ->whereIn('x', [$column - 1, $column, $column + 1] )
      ->get()
      ->filter( function ($token) use ($row, $column) {
          return $token->isAdjacentCoord($row, $column);
        }
      );
  }
  
}
