<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Helpers\Collection;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;
use STIG\Managers\Tokens;

/**
 * Some actions are possible after every one ended their turn : 'Charmer'
 */
trait AfterTurnTrait
{
  public function stAfterTurnNext()
  { 
    self::trace("stAfterTurnNext()");

    $this->addCheckpoint(ST_AFTER_TURN_NEXT_PLAYER);
    $nextPlayerPlay = false;
    $nbPlayers = Players::count();
    $alreadyPlayed = Globals::getAfterTurnPlayers();
    if(!Globals::isModeCompetitive() || count($alreadyPlayed) == $nbPlayers || $this->listPossibleCharmerTokens()->count() == 0){
      //end phase
      $this->gamestate->nextState("end");
      return;
    }
    //Else active next possible player
    if(count($alreadyPlayed) == 0){
      $playerId = Globals::getFirstPlayer();
    }
    else {
      $activePlayer = Players::getActive();
      $playerId = Players::getNextId($activePlayer->id);
    }
    $nextPlayer = Players::get($playerId);
    if(isset($nextPlayer) 
      && $nextPlayer->getZombie() != 1 && $nextPlayer->getEliminated() == 0
    ){
      //For now, this step is only used for playing 'Charmer' special action
      $actionType = ACTION_TYPE_CHARMER;
      $playerAction = PlayerActions::getPlayer($nextPlayer->id,[$actionType])->first();
      if(isset($playerAction) && $playerAction->canBePlayed(0) ){
        Players::changeActive($playerId);
        $nextPlayer->giveExtraTime();
        $nextPlayerPlay = true;
        //$this->addCheckpoint(ST_AFTER_TURN);
      }
    }

    if(!$nextPlayerPlay) {
      $alreadyPlayed[] = $playerId;
      Globals::setAfterTurnPlayers($alreadyPlayed);
      $this->gamestate->nextState("loopback");
    }
    else {
      $this->gamestate->nextState("next");
    }
  }
  
  public function stAfterTurn()
  { 
    self::trace("stAfterTurn()");

    $this->addCheckpoint(ST_AFTER_TURN);
  }

  public function argCharmer1()
  { 
    $possibles = $this->listPossibleCharmerTokens();
    $args  = [
      'tokens' => $possibles->ui(),
    ];
    return $args;
  }

  /**
   * @return Collection
   */
  public function listPossibleCharmerTokens()
  { 
    $possibles = Tokens::getAllRecruits();
    $notPossibles = Globals::getCharmedTokens();
    $possibles = $possibles->filter(function($token) use ($notPossibles){
        return !in_array($token->getId(),$notPossibles); 
      });
    $pids = array_unique($possibles->map(function($token){
      return ($token->getPId()); 
    })->toArray() );
    if(count($pids)<2) return new Collection([]);
    return $possibles;
  }
  
  public function actPass()
  {
    self::checkAction( 'actPass' ); 
    self::trace("actPass()");
    $player = Players::getCurrent();
    $pId = $player->id;
    Notifications::passCharmer($player);
    Globals::setAfterTurnPlayers(array_merge(Globals::getAfterTurnPlayers() ,[$pId]) );
    $this->addCheckpoint(ST_AFTER_TURN);
    $this->gamestate->nextState("nextPlayer");
  }
  /**
   * Special Action 'Charmer' - BEGIN
   */
  public function actCharmer1()
  {
    self::checkAction( 'actCharmer1' ); 
    self::trace("actCharmer1()");

    $player = Players::getCurrent();
    $pId = $player->id;

    $this->addStep($pId,ST_AFTER_TURN_CHARMER_STEP1);
    $this->gamestate->nextState("startCharmer");
  }
  /**
   * Special Action 'Charmer' - EFFECT
   * @param int $tokenId1
   * @param int $tokenId2
   * //TODO JSA avoir UI avec plusieurs zone en upper : une pour chaque zone de recruit-> comme ça on peut cliquer 2 tokens
   * -> ou alors pouvoir cliquer dans la zone de recruit direct
   *  -> un bouton clear Selection
   * -> un multiTOkenSelection (1 par player, mais max 2)
   */
  public function actCharmer2($tokenId1,$tokenId2)
  {
    self::checkAction( 'actCharmer2' ); 
    self::trace("actCharmer2($tokenId1,$tokenId2)");

    $player = Players::getCurrent();
    $pId = $player->id;

    $actionType = ACTION_TYPE_CHARMER;
    $playerAction = PlayerActions::getPlayer($player->id,[$actionType])->first();
    if(!isset($playerAction) || !$playerAction->canBePlayed(0) ){
      throw new UnexpectedException(404,"Not found action $actionType");
    }
    //check token not already charmed in turn
    $charmedTokens = Globals::getCharmedTokens();
    if(in_array($tokenId1,$charmedTokens) || in_array($tokenId2,$charmedTokens)){
      throw new UnexpectedException(135,"You cannot reuse these tokens");
    }
    $token1 = Tokens::get($tokenId1);
    if($token1->location != TOKEN_LOCATION_PLAYER_RECRUIT ){
      throw new UnexpectedException(160,"You cannot take this token");
    }
    $token2 = Tokens::get($tokenId2);
    if($token2->pId == $token1->pId || $token2->location != TOKEN_LOCATION_PLAYER_RECRUIT ){
      throw new UnexpectedException(160,"You cannot take 2 tokens of the same player");
    }

    $charmedTokens[] = $tokenId1;
    $charmedTokens[] = $tokenId2;
    Globals::setCharmedTokens($charmedTokens);
    Globals::setAfterTurnPlayers(array_merge(Globals::getAfterTurnPlayers() ,[$pId]) );
    $p1 = $token1->pId;
    $p2 = $token2->pId;
    $player1 = Players::get($p1);
    $player2 = Players::get($p2);
    $token1->setPId($p2);
    $token2->setPId($p1);
    Notifications::spCharmer($player,$player1,$player2,$token1,$token2);
    Stats::inc("actions_s".$actionType,$pId);
    $playerAction->setNewStateAfterUse();
    Notifications::useActions($player,$playerAction);

    //$this->addStep($pId,ST_AFTER_TURN_CONFIRM_CHOICES);
    $this->gamestate->nextState("next");
  }
  ///**
  // * @param int $tokenDestId
  // */
  //public function actCharmer($tokenDestId)
  //{
  //    self::checkAction( 'actCharmer2' ); 
  //    self::trace("actCharmer2($typeSource)");
//
  //    $player = Players::getCurrent();
  //    $pId = $player->id;
 //
  //    //TODO JSA check token not already charmed in turn
//
  //    //EFFECT
  //    Notifications::spCharmer($player,$player2,$token1,$token2);
//
  //    $this->gamestate->nextState("next");
  //}
}
