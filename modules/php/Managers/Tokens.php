<?php

namespace STIG\Managers;

use STIG\Core\Game;
use STIG\Core\Stats;
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
      ->whereNotLike(static::$prefix . 'location', [TOKEN_LOCATION_CENTRAL_RECRUIT_TOPLACE])
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
        Stats::inc("tokens_deck",$pId,count(STIG_PRIMARY_COLORS)*TOKEN_SETUP_NB);
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
      Stats::inc("tokens_board",$pId,count($schema->start));
    }

    self::create($tokens);

    foreach ($players as $pId => $player) {
        Tokens::shuffleBag($pId);
        //Draw 1 to each recruit zone :
        $token = self::pickOneForLocation(TOKEN_LOCATION_PLAYER_DECK.$pId, TOKEN_LOCATION_PLAYER_RECRUIT, TOKEN_STATE_STIGMERIAN);
        Stats::inc("tokens_deck",$pId, -1);
        Stats::inc("tokens_recruit",$pId);
        if(TOKEN_STIG_YELLOW == $token->getType()){
          $player->addTieBreakerPoints(1);
        }
    }

    return self::getAll();
  }
  /**
   * @param StigmerianToken $token
  */
  public static function createToken($token)
  { 
    /*
    $tokens[] = [
      'type' => $token->getType(),
      'state' => $token->getState(),
      'location' => $token->getLocation(),
      'player_id' => $token->getPId(),
      'y' => $token->getRow(),
      'x' => $token->getCol(),
      'nbr' => 1,
    ];
    
    return self::create($tokens);
    */
    return self::singleCreate($token);
  }
  /**
   * @param int $playerId
  */
  public static function shuffleBag($playerId)
  { 
    self::shuffle(TOKEN_LOCATION_PLAYER_DECK.$playerId);
  }
  /**
   * @param int $playerId
  * @return int nb of tokens on player deck
  */
  public static function countDeck($playerId)
  { 
    return self::DB()
      ->where(static::$prefix . 'location', TOKEN_LOCATION_PLAYER_DECK.$playerId)
      ->wherePlayer($playerId)
      ->count();
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
    //Game::get()->trace("findOnPersonalBoard($playerId,$row, $column)");
    return self::DB()
      ->where(static::$prefix . 'location', TOKEN_LOCATION_PLAYER_BOARD)
      ->wherePlayer($playerId)
      ->where('y', $row)
      ->where('x', $column)
      ->getSingle();
  }
  /**
   * 
   * @param int $row
   * @param int $column
   * @return StigmerianToken if found at that location, null otherwise
   */
  public static function findOnCentralBoard($row, $column)
  { 
    return self::DB()
      ->where(static::$prefix . 'location', TOKEN_LOCATION_CENTRAL_BOARD)
      ->where('y', $row)
      ->where('x', $column)
      ->getSingle();
  }
  /**
   * SEARCH in memory, not in DB
   * @param Collection $boardTokens StigmerianToken
   * @param int $row
   * @param int $column
   * @return StigmerianToken if found at that location, null otherwise
   */
  public static function findTokenOnBoardWithCoord($boardTokens,$row,$column )
  { 
    //Game::get()->trace("findTokenOnBoardWithCoord($row, $column)");
    return $boardTokens->filter( function ($token) use ($row, $column) {
        return $token->row == $row && $token->col ==$column;
      }
    )->first();
  }
  public static function deleteAllAtLocation($location,$playerId)
  { 
    Game::get()->trace("deleteAllOnBoard($location,$playerId)");
    return self::DB()
      ->where(static::$prefix . 'location', $location)
      ->wherePlayer($playerId)
      ->delete()->run();
  }
  
  public static function delete($id)
  { 
    Game::get()->trace("delete($id)");
    return self::DB()->delete($id);
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
      //Order by row, then column -> will be used to avoid sorting this collection when comparing with schema
      ->orderBy('y', 'ASC')
      ->orderBy('x', 'ASC')
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
   * @param int $playerId
   * @param array $token_types (optional) filter on these types
  * @return int nb of tokens on player board recruit zone
  */
  public static function countRecruits($playerId, $token_types = null)
  { 
    $query = self::DB()
      ->where(static::$prefix . 'location', TOKEN_LOCATION_PLAYER_RECRUIT)
      ->wherePlayer($playerId);
    if(isset($token_types)) $query = $query->whereIn('type',$token_types);
    return  $query->count();
  }
  /**
   * 
   * @param array $token_types (optional) filter on these types
   * @return int nb of tokens on player board recruit zone
   */
  public static function countCentralRecruits($token_types = null)
  { 
    $query = self::DB()
      ->where(static::$prefix . 'location', TOKEN_LOCATION_CENTRAL_RECRUIT);
    if(isset($token_types)) $query = $query->whereIn('type',$token_types);
    return  $query->count();
  }
  /**
  * @return Collection of StigmerianToken found at that location
  */
 public static function getAllCentralRecruits()
 { 
   //Game::get()->trace("getAllCentralRecruits()");
   return self::DB()
     ->where(static::$prefix . 'location', TOKEN_LOCATION_CENTRAL_RECRUIT)
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
   * @param array $token_types (optional)
   * @param int $row (optional)
   * @param int $col (optional)
  * @return int nb of tokens on player board filtered by types
  */
  public static function countOnPlayerBoard($playerId,$token_types = null, $row = null,$col =null)
  { 
    $query = self::DB()
      ->where(static::$prefix . 'location', TOKEN_LOCATION_PLAYER_BOARD)
      ->wherePlayer($playerId);
    if(isset($token_types)) $query = $query->whereIn('type',$token_types);
    if(isset($row)) $query = $query->where('y',$row);
    if(isset($col)) $query = $query->where('x',$col);
    return  $query->count();
  }
  
  /**
   * @param int $playerId
   * @param int $row
   * @param int $column
   * @return Collection of StigmerianToken
   */
  public static function listAdjacentTokens($playerId,$row, $column)
  { 
    //Game::get()->trace("listAdjacentTokens($playerId,$row, $column)");
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
   * SEARCH IN MEMORY (no DB)
   * @param Collection $boardTokens tokens already read from DB
   * @param int $row
   * @param int $column
   * @param bool $keepDiagonal
   * @return Collection of StigmerianToken adjacent tokens of 
   */
  public static function listAdjacentTokensOnReadBoard($boardTokens,$row, $column, $keepDiagonal = false)
  { 
    return $boardTokens->filter( function ($token) use ($row, $column, $keepDiagonal) {
          return $token->isAdjacentCoord($row, $column) 
          || $keepDiagonal && $token->isDiagonalAdjacentCoord($row, $column);
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
    //Game::get()->trace("listAdjacentTokensOnCentral($row, $column)");
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

  /**
   * All next picked tokens are described by this order :
   * SELECT type FROM `token` WHERE `token_location` = 'player_deck_2373992'
   *  ORDER BY token_state DESC; 
   * @param int $playerId
   * @return array 
   */
  public static function getOrderedTypesInDeck($playerId)
  { 
    return self::DB()
      ->select(['type'])
      ->where(static::$prefix . 'location', TOKEN_LOCATION_PLAYER_DECK.$playerId)
      ->wherePlayer($playerId)
      ->orderBy(static::$prefix . 'state')
      ->get()
      ->map(function ($token) {
        return $token->type;
      })
      ->toArray();
  }

  /**
   * Keep players with the most tokens of specified types
   * @param Collection $players
   * @param array $types (optional) filter on these types
   * @return array Example ["n" => 4,"pId"=> [1234,  999] ]
   */
  public static function getPlayerIdsWithMaxRecruit($players, $types = null)
  { 
    Game::get()->trace("getPlayerIdsWithMaxRecruit()");
    $maxRecruits = ['n' => 0, 'pId'=> []]; 
    foreach($players as $player_id => $player){
      //--------------------------------------------------
      $recruits = Tokens::countRecruits($player_id, $types);
      if($recruits > $maxRecruits['n']) {
        $maxRecruits['n'] = $recruits;
        $maxRecruits['pId'] = [];
        $maxRecruits['pId'][] = $player_id;
      } else if($recruits == $maxRecruits['n']) {
        $maxRecruits['pId'][] = $player_id;
      }
      //--------------------------------------------------
    }
    return $maxRecruits;
  }
  /**
   * DRAW a token in every player deck until one has more yellow ! and finally put the tokens back in their bags
   * @param array $playersIds 
   * @return int $winTiePlayerId player with the most yellow
   */
  public static function drawUntilYellow($playersIds)
  { 
    Game::get()->trace("drawUntilYellow()".json_encode($playersIds));

    $maxTokens = null;
    $playersTokenTypes = [];
    //counter to increase progressively like if we pick one token at a time:
    $playersYellowTokensCounts = [];
    $winTiePlayerId = $playersIds[array_rand($playersIds)];//to prevent returning null
    foreach($playersIds as $pId){
      $playersTokenTypes[$pId] = Tokens::getOrderedTypesInDeck($pId);
      $maxTokens = isset($maxTokens) ? max($maxTokens, count($playersTokenTypes[$pId]) ) : count($playersTokenTypes[$pId]);
      $playersYellowTokensCounts[$pId] = 0;
    }

    Game::get()->trace("drawUntilYellow() playersTokenTypes:".json_encode($playersTokenTypes));
    for($k=0; $k< $maxTokens;$k++){
      //$yellowFoundAtThisTurn = false;
      $playersYellowFoundAtThisTurn = [];
      foreach($playersIds as $pId){
        if($k >= count($playersTokenTypes[$pId])) continue;
        $type = $playersTokenTypes[$pId][$k];
        if($type == TOKEN_STIG_YELLOW ){
          //$yellowFoundAtThisTurn = true;
          $playersYellowFoundAtThisTurn[] = $pId;
          $winTiePlayerId = $pId;
        }
      }
      if(count($playersYellowFoundAtThisTurn) == 1){
        //We finally have 1 'winner' of the draw -> stop operation
        break;
      }
    }

    //Reshuffle players bags to simulate another randomness, as when players put tokens in the bags IRL
    foreach($playersIds as $pId){
      Tokens::shuffleBag($pId);
    }
      
    return $winTiePlayerId;
  }
}
