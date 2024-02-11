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

    foreach($players as $pId =>$player){
      $score = 0;
      if(in_array($pId,$winnersIds)){
        $player->addPoints($scoreLevel);
        $score += $scoreLevel;
        Notifications::addPoints($player,$scoreLevel,clienttranslate('${player_name} scores ${n} points for the difficulty'));
          
        //TODO JSA Computing score of turn/actions
        $nbRecruits = Tokens::countRecruits($pId);
        if($nbRecruits>0){
          $score += SCORE_PER_RECRUIT*$nbRecruits;
          Notifications::addPoints($player,SCORE_PER_RECRUIT*$nbRecruits,clienttranslate('${player_name} scores ${n} points for remaining tokens in recruit zone'));
        }
      }
      else {
        $player->addPoints(SCORE_FAIL);
        $score += SCORE_FAIL;
        Notifications::addPoints($player,SCORE_FAIL,clienttranslate('${player_name} scores ${n} points for not fulfilling the schema'));
      }
      if($player->isJokerUsed()){
        $score += SCORE_JOKER_USED;
        Notifications::addPoints($player,SCORE_JOKER_USED,clienttranslate('${player_name} scores ${n} points for using the joker'));
      }
    }
    
    $this->gamestate->nextState('next');
  }
  
  public function stPreEndOfGame()
  {
    Notifications::message('Game is ending...');
    $this->gamestate->nextState('next');
  }
}
