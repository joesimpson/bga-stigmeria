<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Managers\Players;
use STIG\Managers\Schemas;
use STIG\Managers\Tokens;

trait ScoringTrait
{
  
  public function stScoring()
  {
    //Schema is chosen at round start => so it is scored at round end
    //$this->computeSchemaScoring();

    $this->gamestate->nextState('next');
  }


  public function computeSchemaScoring()
  {
    self::trace("computeSchemaScoring()");
    $turn = Globals::getTurn();
    $schema = Schemas::getCurrentSchema();
    $winnersIds = Globals::getWinnersIds();
    $players = Players::getAll();
    //NORMAL MODE SCORING :
    $scoreLevel = 0;
    switch($schema->difficulty){
      case OPTION_DIFFICULTY_1:
        $scoreLevel = SCORE_DIFFICULTY_1;
        break;
      case OPTION_DIFFICULTY_2:
        $scoreLevel = SCORE_DIFFICULTY_2;
        break;
      case OPTION_DIFFICULTY_3:
        $scoreLevel = SCORE_DIFFICULTY_3;
        break;
      case OPTION_DIFFICULTY_4:
        $scoreLevel = SCORE_DIFFICULTY_4;
        break;
    }

    $nextTurns = 0;
    $nextActions = 0;
    $k = $turn + 1;
    while($k<=TURN_MAX){
      //Each turn provides K actions -> max actions = 55
      $nextActions += $k;
      $nextTurns++;
      $k++;
    }

    foreach($players as $pId =>$player){
      $score = 0;
      if(in_array($pId,$winnersIds)){
        $score += $scoreLevel;
        Notifications::addPoints($player,$scoreLevel,clienttranslate('${player_name} scores ${n} points for the difficulty'));
            
        if(!Globals::isModeDiscovery()){
          $nbActions = $nextActions + $player->countRemainingPersonalActions();
          $scoreActions = SCORE_PER_ACTION * $nbActions;
          if($scoreActions>0){
            $score += $scoreActions;
            Notifications::addPoints($player,$scoreActions,clienttranslate('${player_name} scores ${n} points for remaining actions : ${n2} remaining turns and ${n3} actions unused in this turn'),$nextTurns,$player->countRemainingPersonalActions());
          }

          $scoreRecruits = SCORE_PER_RECRUIT*Tokens::countRecruits($pId);
          if($scoreRecruits>0){
            $score += $scoreRecruits;
            Notifications::addPoints($player,$scoreRecruits,clienttranslate('${player_name} scores ${n} points for remaining tokens in recruit zone'));
          }
          
        }
      }
      else {
        $score += SCORE_FAIL;
        Notifications::addPoints($player,SCORE_FAIL,clienttranslate('${player_name} scores ${n} points for not fulfilling the schema'));
      }
      if($player->isJokerUsed()){
        $score += SCORE_JOKER_USED;
        Notifications::addPoints($player,SCORE_JOKER_USED,clienttranslate('${player_name} scores ${n} points for using the joker'));
      }
      $player->addPoints($score);
      //TiE BREAKER:
      $yellowTokens = Tokens::countRecruits($pId,[TOKEN_STIG_YELLOW]);
      $player->setTieBreakerPoints($yellowTokens);
    }
  }
  
  public function stPreEndOfGame()
  {
    Notifications::message(clienttranslate('Game is ending...'));
    $this->gamestate->nextState('next');
  }
}
