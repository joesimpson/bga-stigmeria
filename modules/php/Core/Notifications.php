<?php

namespace STIG\Core;

use STIG\Managers\Players;
use STIG\Helpers\Utils;
use STIG\Core\Globals;
use STIG\Models\Player;
use STIG\Models\Schema;
use STIG\Models\StigmerianToken;

class Notifications
{ 

  /**
   * @param int $round
   * @param Schema $schema
   * @param Collection $tokens
   */
  public static function newRound($round,$schema,$tokens){
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
   * @param int $actionCost
   */
  public static function drawToken($player, $token, $actionCost){
    self::notifyAll('drawToken',clienttranslate('${player_name} draws a new stigmerian to the recruitment zone (cost : ${n} actions)'),[ 
        'player' => $player,
        'token' => $token->getUiData(),
        'n' => $actionCost,
      ],
    );
  }
  /**
   * @param Player $player
   * @param StigmerianToken $token
   */
  public static function newPollen($player, $token){
    self::notifyAll('newPollen',clienttranslate('${player_name} gets a new pollen at ${L}'),[ 
        'player' => $player,
        'token' => $token->getUiData(),
        'L' => $token->getCoordName(),
      ],
    );
  }
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param int $actionCost
   */
  public static function moveToCentralBoard($player, $token, $actionCost){
    self::notifyAll('moveToCentralBoard',clienttranslate('${player_name} places a new stigmerian on StigmaReine at ${L} (cost : ${n} actions)'),[ 
        'player' => $player,
        'token' => $token->getUiData(),
        'L' => $token->getCoordName(),
        'n' => $actionCost,
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
    self::notifyAll('moveOnCentralBoard',clienttranslate('${player_name} moves a stigmerian on StigmaReine from ${A} to ${B} (cost : ${n} actions)'),[ 
        'player' => $player,
        'token' => $token->getUiData(),
        'A' => $from,
        'B' => $to,
        'n' => $actionCost,
      ],
    );
  }
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param int $actionCost
   */
  public static function moveToPlayerBoard($player, $token, $actionCost){
    self::notifyAll('moveToPlayerBoard',clienttranslate('${player_name} places a new stigmerian at ${L} (cost : ${n} actions)'),[ 
        'player' => $player,
        'token' => $token->getUiData(),
        'L' => $token->getCoordName(),
        'n' => $actionCost,
      ],
    );
  }
  
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param int $actionCost
   */
  public static function moveFromDeckToPlayerBoard($player, $token, $actionCost){
    self::notifyAll('moveFromDeckToPlayerBoard',clienttranslate('${player_name} places a new stigmerian at ${L} (cost : ${n} actions)'),[ 
        'player' => $player,
        'token' => $token->getUiData(),
        'L' => $token->getCoordName(),
        'n' => $actionCost,
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
    self::notifyAll('moveOnPlayerBoard',clienttranslate('${player_name} moves a ${color} stigmerian from ${A} to ${B} (cost : ${n} actions)'),[ 
        'i18n' => [ 'color'],
        'player' => $player,
        'color' => StigmerianToken::getTypeName($token->getType()),
        'token' => $token->getUiData(),
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
    self::notifyAll('moveBackToBox',clienttranslate('${player_name} moves a ${color} stigmerian out of their board from ${L}: it is now in the game box (cost : ${n} actions)'),[ 
        'i18n' => [ 'color'],
        'player' => $player,
        'color' => StigmerianToken::getTypeName($token->getType()),
        'token' => $token->getUiData(),
        'L' => $from,
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
  public static function moveBackToRecruit($player, $token,$from, $actionCost){
    self::notifyAll('moveBackToRecruit',clienttranslate('${player_name} moves a ${color} stigmerian out of their board from ${L}: it is now in recruit zone (cost : ${n} actions)'),[ 
        'i18n' => [ 'color'],
        'player' => $player,
        'color' => StigmerianToken::getTypeName($token->getType()),
        'token' => $token->getUiData(),
        'L' => $from,
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
  public static function moveToCentralRecruit($player, $token,$from, $actionCost){
    self::notifyAll('moveToCentralRecruit',clienttranslate('${player_name} moves a ${color} stigmerian out of the central board from ${L}: it is now in StigmaReine recruit zone'),[ 
        'i18n' => [ 'color'],
        'player' => $player,
        'color' => StigmerianToken::getTypeName($token->getType()),
        'token' => $token->getUiData(),
        'L' => $from,
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
    self::notifyAll('spSwap',clienttranslate('${player_name} swaps 2 stigmerians at ${A} and ${B} (cost : ${n} actions)'),[ 
        'player' => $player,
        'A' => $token1->getCoordName(),
        'B' => $token2->getCoordName(),
        't1' => $token1->getUiData(),
        't2' => $token2->getUiData(),
        'n' => $actionCost,
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
    $color1 = StigmerianToken::getTypeName($typeSource);
    $color2 = StigmerianToken::getTypeName($typeDest);
    self::notifyAll('playJoker',clienttranslate('${player_name} plays the unique joker to change ${n} ${color1} stigmerians to ${color2}'),[ 
        'i18n' => ['color1','color2'],  
        'player' => $player,
        'n' => count($tokens),
        'color1' => $color1,
        'color2' => $color2,
        'tokens' => $tokens,
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
  public static function spMerge($player,$token1,$token2,$actionCost){
    self::notifyAll('spMerge',clienttranslate('${player_name} merges 2 tokens at ${L1} and ${L2} (cost: ${n} actions)'),[ 
        'player' => $player,
        'L1' => $token1->getCoordName(),
        'L2' => $token2->getCoordName(),
        'token1' => $token1->getUiData(),
        'token2' => $token2->getUiData(),
        'n' => $actionCost,
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
    self::notifyAll('spCombination',clienttranslate('${player_name} use the Combination action to transform a ${color} stigmerian at ${L1} into a ${color2} stigmerian (cost: ${n} actions)'),[ 
        'i18n' => ['color','color2'],
        'player' => $player,
        'L1' => $token->getCoordName(),
        'color' => StigmerianToken::getTypeName($previousColor),
        'color2' => StigmerianToken::getTypeName($token->getType()),
        'token' => $token->getUiData(),
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
    self::notifyAll('spWhite',clienttranslate('${player_name} use the white action to merge stigmerians at ${L1} and ${L2} into ${L1} (cost: ${n} actions)'),[ 
        'player' => $player,
        'L1' => $token1->getCoordName(),
        'L2' => $token2->getCoordName(),
        'token1' => $token1->getUiData(),
        'token2' => $token2->getUiData(),
        'n' => $actionCost,
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
    self::notifyAll('spBlack',clienttranslate('${player_name} use the Quarter Note action to split the white stigmerian at ${L1} into 2 black stigmerians at ${L1} and ${L2} (cost: ${n} actions)'),[ 
        'player' => $player,
        'L1' => $token1->getCoordName(),
        'L2' => $token2->getCoordName(),
        'token1' => $token1->getUiData(),
        'token2' => $token2->getUiData(),
        'n' => $actionCost,
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
    self::notifyAll('spTwoBeats',clienttranslate('${player_name} use the Two Beats action to get a new white stigmerian at ${L1} (cost: ${n} actions)'),[ 
        'player' => $player,
        'L1' => $token->getCoordName(),
        'token' => $token->getUiData(),
        'n' => $actionCost,
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
    self::notifyAll('spRest',clienttranslate('${player_name} use the Rest action to remove a token at ${L1} (cost: ${n} actions)'),[ 
        'player' => $player,
        'L1' => $token->getCoordName(),
        'token' => $token->getUiData(),
        'n' => $actionCost,
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
    self::notifyAll('emptyNotif','',[],);
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
}
