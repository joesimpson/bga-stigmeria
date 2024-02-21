<?php

namespace STIG\Core;

use STIG\Managers\Players;
use STIG\Helpers\Utils;
use STIG\Core\Globals;
use STIG\Models\Player;
use STIG\Models\PlayerAction;
use STIG\Models\Schema;
use STIG\Models\StigmerianToken;

class Notifications
{ 

  /**
   * @param int $round
   * @param Schema $schema
   * @param Collection $tokens
   * @param Collection $actions
   */
  public static function newRound($round,$schema,$tokens,$actions){
    //reload players datas - for now getUiData doesn't care about this current player id, but be careful in the future !
    $players = Players::getUiData(null);
    $msg = clienttranslate('Starting round #${n} with schema #${s}');
    if($round==1) $msg = '';
    if($round==1 && $schema->start->count()>0) $msg = clienttranslate('Players boards are initialized by schema #${s}');
    self::notifyAll('newRound',$msg,[ 
        'n' => $round,
        's' => $schema->id,
        //No need to send all the schema datas if we send it at start
        //'schema' => $schema->getUiData(),
        'tokens' => $tokens,
        'players' => $players,
        'actions' => $actions,
      ],
    );
  }
  /**
   * @param array $winds
   */
  public static function newWinds($winds){
    self::notifyAll('newWinds','',[
      'winds' => $winds,
    ],);
  }
  /**
   * @param int $turn
   */
  public static function newTurn($turn){
    self::notifyAll('newTurn',clienttranslate('Starting turn #${n}'),[ 
        'n' => $turn,
      ],
    );
  }
  /**
   * @param int $turn
   */
  public static function lastTurnEnd($turn){
    self::notifyAll('lastTurnEnd',clienttranslate('Game ends because the last turn has ended'),[ 
        'n' => $turn,
      ],
    );
  }
  
  /**
   * @param int $turn
   * @param Player $player
   * @param int $subcase used to choose player -> now used to change message
   */
  public static function updateFirstPlayer($turn,$player,$subcase)
  {
    switch($subcase){
      case 1: 
        $msg= clienttranslate('${player_name} is the starting player (most tokens in recruit zone)');
        break;
      case 2: 
        $msg= clienttranslate('${player_name} is the starting player (most yellow tokens in recruit zone)');
        break;
      case 3:
        $msg= clienttranslate('${player_name} is the starting player (most yellow tokens drawn from bags)');
        break;
    }
    if($turn == 1){
      $msg= clienttranslate('${player_name} is the starting player');
    }
    self::notifyAll('updateFirstPlayer', $msg, [
      'player' => $player,
    ]);
  }

  /**
   * @param Player $player1
   * @param Player $player2
   */
  public static function letNextPlay($player1,$player2){
    self::notifyAll('letNextPlay',clienttranslate('${player_name} lets ${player_name2} start to play for this turn'),[ 
        'player' => $player1,
        'player2' => $player2,
      ],
    );
  }
  /**
   * @param Player $player
   * @param int $turn
   */
  public static function startTurn($player,$turn){
    self::notifyAll('startTurn',clienttranslate('${player_name} enters turn #${n}'),[ 
        'player' => $player,
        'n' => $turn,
      ],
    );
  }
  /**
   * @param Player $player
   */
  public static function endTurn($player){
    self::notifyAll('endTurn',clienttranslate('${player_name} ends his turn'),[ 
        'player' => $player,
      ],
    );
  }

  /**
   * Update number of actions
   * @param Player $player
   */
  public static function useActions($player){
    self::notifyAll('useActions','',[ 
        'player' => $player,
        'ncad' => $player->getNbCommonActionsDone(),
        'npad' => $player->getNbPersonalActionsDone(),
        'npan' => $player->countRemainingPersonalActions(),
      ],
    );
  }
  /**
   * @param Player $player
   * @param StigmerianToken $token
   */
  public static function drawTokenForCentral($player, $token){
    self::notifyAll('drawTokenForCentral',clienttranslate('${player_name} draws a new ${token_color} stigmerian to be placed on StigmaReine'),[ 
        'player' => $player,
        'token' => $token->getUiData(),
        'preserve' => [ 'token_type' ],
        'token_color' => StigmerianToken::getTypeName($token->getType()),
        'token_type' => $token->getType(),
      ],
    );
  }
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param int $actionCost
   */
  public static function drawToken($player, $token, $actionCost){
    self::notifyAll('drawToken',clienttranslate('${player_name} draws a new ${token_color} stigmerian and place it in the recruitment zone (cost : ${n} actions)'),[ 
        'player' => $player,
        'token' => $token->getUiData(),
        'n' => $actionCost,
        'preserve' => [ 'token_type' ],
        'token_color' => StigmerianToken::getTypeName($token->getType()),
        'token_type' => $token->getType(),
      ],
    );
  }
  /**
   * @param Player $player
   * @param StigmerianToken $token
   */
  public static function newPollen($player, $token){
    self::notifyAll('newPollen',clienttranslate('${player_name} gets a new ${token_color} pollen at ${L}'),[ 
        'player' => $player,
        'token' => $token->getUiData(),
        'L' => $token->getCoordName(),
        'preserve' => [ 'token_type' ],
        'token_color' => StigmerianToken::getTypeName($token->getType()),
        'token_type' => $token->getType(),
      ],
    );
  }
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param int $actionCost
   */
  public static function moveToCentralBoard($player, $token, $actionCost){
    self::notifyAll('moveToCentralBoard',clienttranslate('${player_name} places a new ${token_color} stigmerian on StigmaReine at ${L} (cost : ${n} actions)'),[ 
        'player' => $player,
        'token' => $token->getUiData(),
        'L' => $token->getCoordName(),
        'n' => $actionCost,
        'preserve' => [ 'token_type' ],
        'token_color' => StigmerianToken::getTypeName($token->getType()),
        'token_type' => $token->getType(),
      ],
    );
  }
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param string $from Coordinate name
   * @param string $to Coordinate name
   * @param int $actionCost
   */
  public static function moveOnCentralBoard($player, $token,$from,$to, $actionCost){
    self::notifyAll('moveOnCentralBoard',clienttranslate('${player_name} moves a ${token_color} stigmerian on StigmaReine from ${A} to ${B} (cost : ${n} actions)'),[ 
        'player' => $player,
        'token' => $token->getUiData(),
        'A' => $from,
        'B' => $to,
        'n' => $actionCost,
        'preserve' => [ 'token_type' ],
        'token_color' => StigmerianToken::getTypeName($token->getType()),
        'token_type' => $token->getType(),
      ],
    );
  }
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param int $actionCost
   */
  public static function moveToPlayerBoard($player, $token, $actionCost){
    self::notifyAll('moveToPlayerBoard',clienttranslate('${player_name} places a new ${token_color} stigmerian at ${L} (cost : ${n} actions)'),[ 
        'player' => $player,
        'token' => $token->getUiData(),
        'L' => $token->getCoordName(),
        'n' => $actionCost,
        'preserve' => [ 'token_type' ],
        'token_color' => StigmerianToken::getTypeName($token->getType()),
        'token_type' => $token->getType(),
      ],
    );
  }
  
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param int $actionCost
   */
  public static function moveFromDeckToPlayerBoard($player, $token, $actionCost){
    self::notifyAll('moveFromDeckToPlayerBoard',clienttranslate('${player_name} places a new ${token_color} stigmerian at ${L} (cost : ${n} actions)'),[ 
        'player' => $player,
        'token' => $token->getUiData(),
        'L' => $token->getCoordName(),
        'n' => $actionCost,
        'preserve' => [ 'token_type' ],
        'token_color' => StigmerianToken::getTypeName($token->getType()),
        'token_type' => $token->getType(),
      ],
    );
  }
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param string $from Coordinate name
   * @param string $to Coordinate name
   * @param int $actionCost
   */
  public static function moveOnPlayerBoard($player, $token,$from,$to, $actionCost){
    self::notifyAll('moveOnPlayerBoard',clienttranslate('${player_name} moves a ${token_color} stigmerian from ${A} to ${B} (cost : ${n} actions)'),[ 
        'i18n' => [ 'token_color'],
        'preserve' => [ 'token_type' ],
        'player' => $player,
        'token_color' => StigmerianToken::getTypeName($token->getType()),
        'token' => $token->getUiData(),
        'token_type' => $token->getType(),
        'A' => $from,
        'B' => $to,
        'n' => $actionCost,
      ],
    );
  }
  
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param string $from Coordinate name
   * @param int $actionCost
   */
  public static function moveBackToBox($player, $token,$from, $actionCost){
    self::notifyAll('moveBackToBox',clienttranslate('${player_name} moves a ${token_color} stigmerian out of their board from ${L}: it is now in the game box (cost : ${n} actions)'),[ 
        'i18n' => [ 'color'],
        'player' => $player,
        'token' => $token->getUiData(),
        'L' => $from,
        'n' => $actionCost,
        'preserve' => [ 'token_type' ],
        'token_color' => StigmerianToken::getTypeName($token->getType()),
        'token_type' => $token->getType(),
      ],
    );
  }
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param string $from Coordinate name
   * @param int $actionCost
   */
  public static function moveBackToRecruit($player, $token,$from, $actionCost){
    self::notifyAll('moveBackToRecruit',clienttranslate('${player_name} moves a ${token_color} stigmerian out of their board from ${L}: it is now in recruit zone (cost : ${n} actions)'),[ 
        'i18n' => [ 'token_color'],
        'player' => $player,
        'token' => $token->getUiData(),
        'L' => $from,
        'n' => $actionCost,
        'preserve' => [ 'token_type' ],
        'token_color' => StigmerianToken::getTypeName($token->getType()),
        'token_type' => $token->getType(),
      ],
    );
  }
  
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param string $from Coordinate name
   * @param int $actionCost
   */
  public static function moveToCentralRecruit($player, $token,$from, $actionCost){
    self::notifyAll('moveToCentralRecruit',clienttranslate('${player_name} moves a ${token_color} stigmerian out of the central board from ${L}: it is now in StigmaReine recruit zone'),[ 
        'i18n' => [ 'token_color'],
        'player' => $player,
        'token' => $token->getUiData(),
        'L' => $from,
        'preserve' => [ 'token_type' ],
        'token_color' => StigmerianToken::getTypeName($token->getType()),
        'token_type' => $token->getType(),
      ],
    );
  }
  /**
   * @param Player $player
   * @param StigmerianToken $token1
   * @param StigmerianToken $token2
   * @param int $actionCost
   */
  public static function spSwap($player,$token1,$token2, $actionCost){
    self::notifyAll('spSwap',clienttranslate('${player_name} swaps 2 ${token_color}${token_color2}stigmerians at ${A} and ${B} (cost : ${n} actions)'),[ 
        'player' => $player,
        'A' => $token1->getCoordName(),
        'B' => $token2->getCoordName(),
        't1' => $token1->getUiData(),
        't2' => $token2->getUiData(),
        'n' => $actionCost,
        'preserve' => [ 'token_type','token_type2' ],
        'token_color' => StigmerianToken::getTypeName($token1->getType()),
        'token_type' => $token1->getType(),
        'token_color2' => StigmerianToken::getTypeName($token2->getType()),
        'token_type2' => $token2->getType(),
      ],
    );
  }

  /**
   * @param Player $player
   * @param int $typeSource
   * @param int $typeDest
   * @param Collection $newTokens StigmerianToken
   */
  public static function playJoker($player,$typeSource, $typeDest, $newTokens){
    $tokens = $newTokens->ui();
    self::notifyAll('playJoker',clienttranslate('${player_name} plays the unique joker to change ${n} ${token_color} stigmerians to ${token_color2}'),[ 
        'i18n' => ['token_color','token_color2'],  
        'player' => $player,
        'n' => count($tokens),
        'tokens' => $tokens,
        'preserve' => [ 'token_type','token_type2' ],
        'token_color' => StigmerianToken::getTypeName($typeSource),
        'token_type' => $typeSource,
        'token_color2' => StigmerianToken::getTypeName($typeDest),
        'token_type2' => $typeDest,
      ],
    );
  }
  /**
   * 
   * @param Player $player
   * @param StigmerianToken $token1
   * @param StigmerianToken $token2
   * @param int $actionCost
   */
  public static function spMixing($player,$token1,$token2,$actionCost){
    self::notifyAll('spMixing',clienttranslate('${player_name} mixes 2 tokens ${token_color}${token_color2} at ${L1} and ${L2} (cost: ${n} actions)'),[ 
        'player' => $player,
        'L1' => $token1->getCoordName(),
        'L2' => $token2->getCoordName(),
        'token1' => $token1->getUiData(),
        'token2' => $token2->getUiData(),
        'n' => $actionCost,
        'preserve' => [ 'token_type','token_type2' ],
        'token_color' => StigmerianToken::getTypeName($token1->getType()),
        'token_type' => $token1->getType(),
        'token_color2' => StigmerianToken::getTypeName($token2->getType()),
        'token_type2' => $token2->getType(),
      ],
    );
  }
  
  /**
   * 
   * @param Player $player
   * @param StigmerianToken $token
   * @param int $actionCost
   */
  public static function spCombination($player,$token,$previousColor,$actionCost){
    self::notifyAll('spCombination',clienttranslate('${player_name} use the Combination action to transform a ${token_color} stigmerian at ${L1} into a ${token_color2} stigmerian (cost: ${n} actions)'),[ 
        'i18n' => ['color','color2'],
        'player' => $player,
        'L1' => $token->getCoordName(),
        'token' => $token->getUiData(),
        'n' => $actionCost,
        'preserve' => [ 'token_type','token_type2' ],
        'token_color' => StigmerianToken::getTypeName($previousColor),
        'token_type' => $previousColor,
        'token_color2' => StigmerianToken::getTypeName($token->getType()),
        'token_type2' => $token->getType(),
      ],
    );
  }
  /**
   * 
   * @param Player $player
   * @param int $nbTokens
   * @param int $actionCost
   */
  public static function spFulgurance($player,$nbTokens,$actionCost){
    self::notifyAll('spFulgurance',clienttranslate('${player_name} uses the Fulgurance to draw and place ${n} tokens from the deck (cost: ${n2} actions)'),[ 
        'player' => $player,
        'n' => $nbTokens,
        'n2' => $actionCost,
      ],
    );
  }
  
  /**
   * 
   * @param Player $player
   * @param int $actionCost
   */
  public static function spFastMove($player,$actionCost){
    self::notifyAll('spFastMove',clienttranslate('${player_name} uses the Fast Step (cost: ${n} actions)'),[ 
        'player' => $player,
        'n' => $actionCost,
      ],
    );
  }
  
  /**
   * 
   * @param Player $player
   * @param int $actionCost
   */
  public static function spDiagonal($player,$actionCost){
    self::notifyAll('spDiagonal',clienttranslate('${player_name} uses the Diagonal action (cost: ${n} actions)'),[ 
        'player' => $player,
        'n' => $actionCost,
      ],
    );
  }
  /**
   * 
   * @param Player $player
   * @param int $nbTokens
   * @param int $actionCost
   */
  public static function spChoreography($player,$nbTokens,$actionCost){
    self::notifyAll('spChoreography',clienttranslate('${player_name} uses the Choreography to move a maximum of ${n} stigmerians (cost: ${n2} actions)'),[ 
        'player' => $player,
        'n' => $nbTokens,
        'n2' => $actionCost,
      ],
    );
  }
  
  /**
   * 
   * @param Player $player
   * @param StigmerianToken $token1
   * @param StigmerianToken $token2
   * @param int $actionCost
   */
  public static function spWhite($player,$token1,$token2,$actionCost){
    self::notifyAll('spWhite',clienttranslate('${player_name} use the Half Note action to merge 2 ${token_color}${token_color} stigmerians at ${L1} and ${L2} into a ${token_color2} ${L1} (cost: ${n} actions)'),[ 
        'player' => $player,
        'L1' => $token1->getCoordName(),
        'L2' => $token2->getCoordName(),
        'token1' => $token1->getUiData(),
        'token2' => $token2->getUiData(),
        'n' => $actionCost,
        'preserve' => [ 'token_type','token_type2' ],
        //previous color
        'token_color' => StigmerianToken::getTypeName(TOKEN_STIG_BLACK),
        'token_type' => TOKEN_STIG_BLACK,
        'token_color2' => StigmerianToken::getTypeName(TOKEN_STIG_WHITE),
        'token_type2' => TOKEN_STIG_WHITE,
      ],
    );
  }
  
  /**
   * 
   * @param Player $player
   * @param StigmerianToken $token1
   * @param StigmerianToken $token2
   * @param int $actionCost
   */
  public static function spBlack($player,$token1,$token2,$actionCost){
    self::notifyAll('spBlack',clienttranslate('${player_name} use the Quarter Note action to split the ${token_color} stigmerian at ${L1} into 2 ${token_color2}${token_color2} stigmerians at ${L1} and ${L2} (cost: ${n} actions)'),[ 
        'player' => $player,
        'L1' => $token1->getCoordName(),
        'L2' => $token2->getCoordName(),
        'token1' => $token1->getUiData(),
        'token2' => $token2->getUiData(),
        'n' => $actionCost,
        'preserve' => [ 'token_type','token_type2' ],
        //previous color
        'token_color' => StigmerianToken::getTypeName(TOKEN_STIG_WHITE),
        'token_type' => TOKEN_STIG_WHITE,
        'token_color2' => StigmerianToken::getTypeName($token2->getType()),
        'token_type2' => $token2->getType(),
      ],
    );
  }
  
  /**
   * 
   * @param Player $player
   * @param StigmerianToken $token
   * @param int $actionCost
   */
  public static function spTwoBeats($player,$token,$actionCost){
    self::notifyAll('spTwoBeats',clienttranslate('${player_name} use the Two Beats action to get a new ${token_color} stigmerian at ${L1} (cost: ${n} actions)'),[ 
        'player' => $player,
        'L1' => $token->getCoordName(),
        'token' => $token->getUiData(),
        'n' => $actionCost,
        'preserve' => [ 'token_type' ],
        'token_color' => StigmerianToken::getTypeName($token->getType()),
        'token_type' => $token->getType(),
      ],
    );
  }
  
  /**
   * 
   * @param Player $player
   * @param StigmerianToken $token
   * @param int $actionCost
   */
  public static function spRest($player,$token,$actionCost){
    self::notifyAll('spRest',clienttranslate('${player_name} use the Rest action to remove a ${token_color} ${token_type} at ${L1} (cost: ${n} actions)'),[ 
        'player' => $player,
        'L1' => $token->getCoordName(),
        'token' => $token->getUiData(),
        'n' => $actionCost,
        'token_color' => StigmerianToken::getTypeName($token->getType()),
        'token_type' => $token->getType(),

      ],
    );
  }
  
  /**
   * Will Unlock Special action 
   * @param Player $player
   * @param int $nbActions
   * @param int $nbAlignedTokens
   */
  public static function gainSp($player,$nbActions, $nbAlignedTokens){
    self::notifyAll('gainSp',clienttranslate('${player_name} gains ${n} special action after aligning ${n2} tokens'),[ 
        'player' => $player,
        'n' => $nbActions,
        'n2' => $nbAlignedTokens,
      ],
    );
  }
  /**
   * Unlock Special action !
   * @param Player $player
   * @param PlayerAction $action
   */
  public static function unlockSp($player,$action){
    self::notifyAll('unlockSp',clienttranslate('${player_name} unlocks a special action : ${action_name}'),[ 
        'i18n' => ['action_name'],
        'player' => $player,
        'action' => $action->getUiData(),
        'action_name' => $action->getName(),
      ],
    );
  }
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param Player $playerDestination
   */
  public static function putTokenInBag($player,$token,$playerDestination){
    self::notifyAll('putTokenInBag',clienttranslate('${player_name} puts a ${token_color} stigmerian in ${player_name2} bag'),[ 
        'player' => $player,
        'player2' => $playerDestination,
        'token' => $token->getUiData(),
        'preserve' => [ 'token_type' ],
        'token_color' => StigmerianToken::getTypeName($token->getType()),
        'token_type' => $token->getType(),
      ],
    );
  }
  /**
   * @param string $windDir
   * @param Collection $boardTokens StigmerianToken 
   * @param Player $player player board or null if central
   */
  public static function windBlows($windDir,$boardTokens,$player = null){
    $tokens = $boardTokens->ui();
    $nbTokens = count($tokens);
    
    if(isset($player)){
      $message = clienttranslate('Wind blows to ${dir} on ${player_name} flower and move ${n} tokens');
    } else {//CENTRAL
      $message = clienttranslate('Wind blows to ${dir} on StigmaReine flower and move ${n} tokens');
    }

    self::notifyAll('windBlows',$message,[ 
        'i18n'=>['dir'],
        'player' => $player,
        'tokens' => $tokens,
        'windDir' => $windDir,
        'dir' => Globals::getWindDirName($windDir),
        'n' => $nbTokens,
      ],
    );
  }
  
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param string $fromCoord
   */
  public static function windElimination($player,$token,$fromCoord){
    $message = clienttranslate('Wind eliminates ${player_name} by moving a token out of their board (from ${coord}) !');
    self::notifyAll('windElimination',$message,[ 
        'player' => $player,
        'coord' => $fromCoord,
      ],
    );
  }
  
  
  /**
   * @param Player $player
   */
  public static function schemaFulfilled($player){
    self::notifyAll('schemaFulfilled',clienttranslate('${player_name} successfully fulfilled the schema !'),[ 
        'player' => $player,
      ],
    );
  }
  
  /**
   * @param Player $player
   * @param int $points
   * @param string $msg (optional)
   * @param int $number2 (optional) 2nd number used in custom message
   * @param int $number3 (optional) 3rd number used in custom message
   */
  public static function addPoints($player,$points, $msg = null, $number2 = null, $number3 = null){
    if(!isset($msg)) $msg = clienttranslate('${player_name} scores ${n} points');
    self::notifyAll('addPoints',$msg,[ 
        'player' => $player,
        'n' => $points,
        'n2' => $number2,
        'n3' => $number3,
      ],
    );
  }
  /*************************
   **** GENERIC METHODS ****
   *************************/
  protected static function notifyAll($name, $msg, $data)
  {
    self::updateArgs($data);
    Game::get()->notifyAllPlayers($name, $msg, $data);
  }

  protected static function notify($player, $name, $msg, $data)
  {
    $pId = is_int($player) ? $player : $player->getId();
    self::updateArgs($data);
    Game::get()->notifyPlayer($pId, $name, $msg, $data);
  }

  public static function message($txt, $args = [])
  {
    self::notifyAll('message', $txt, $args);
  }

  public static function messageTo($player, $txt, $args = [])
  {
    $pId = is_int($player) ? $player : $player->getId();
    self::notify($pId, 'message', $txt, $args);
  }

  /**
   *  Empty notif to send after an action, to let framework works & refresh ui
   * (Usually not needed if we send another notif or if we change state of a player)
   * */
  public static function emptyNotif(){
    self::notifyAll('e','',[],);
  }
  /*********************
   **** UPDATE ARGS ****
   *********************/

  /*
   * Automatically adds some standard field about player and/or card
   */
  protected static function updateArgs(&$data)
  {
    if (isset($data['player'])) {
      $data['player_name'] = $data['player']->getName();
      $data['player_id'] = $data['player']->getId();
      unset($data['player']);
    }

    if (isset($data['player2'])) {
      $data['player_name2'] = $data['player2']->getName();
      $data['player_id2'] = $data['player2']->getId();
      unset($data['player2']);
    }
    
    if (array_key_exists('n2',$data) && $data['n2'] == null) {
      unset($data['n2']);
    }
    if (array_key_exists('n3',$data) && $data['n3'] == null) {
      unset($data['n3']);
    }
  }


  //UPDATES after confirm/undo :
  
  public static function refreshUI($pId,$datas)
  {
    // // Keep only the things from getAllDatas that matters
    $fDatas = [
      'players' => $datas['players'],
      'actions' => $datas['actions'],
      'tokens' => $datas['tokens'],
    ];

    self::notifyAll('refreshUI', '', [
      'player_id' => $pId,
      'datas' => $fDatas,
    ]);
  }
  public static function clearTurn($player, $notifIds)
  {
    self::notifyAll('clearTurn', '', [
      'player' => $player,
      'notifIds' => $notifIds,
    ]);
  }
  public static function undoStep($player, $stepId)
  {
    self::notifyAll('undoStep', clienttranslate('${player_name} undoes their action'), [
      'player' => $player,
    ]);
  }
  public static function restartTurn($player)
  {
    self::notifyAll('restartTurn', clienttranslate('${player_name} restarts their turn'), [
      'player' => $player,
    ]);
  }

  
}
