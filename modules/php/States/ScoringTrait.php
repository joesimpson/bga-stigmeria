<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Managers\Players;
use STIG\Managers\Schemas;

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
      if(in_array($pId,$winnersIds)){
        $player->addPoints($scoreLevel);
        Notifications::addPoints($player,$scoreLevel);
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
