<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Helpers\Utils;
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

    $nextTurnActions = Utils::calcFutureTurnsActions($turn);
    $nextTurns = $nextTurnActions['nextTurns'];
    $nextActions = $nextTurnActions['nextActions'];

    foreach($players as $pId =>$player){
      $score = 0;
      $remainingActions = $player->countRemainingPersonalActions();
      if(Globals::isModeContinueToLastTurn()){
        $nextTurnActions = Utils::calcFutureTurnsActions($player->getLastTurn());
        $nextTurns = $nextTurnActions['nextTurns'];
        $nextActions = $nextTurnActions['nextActions'];
        $remainingActions = Utils::countRemainingActionsInTurn($player->getLastTurn(),$player->getNbPersonalActionsDone());
      }
      if(in_array($pId,$winnersIds)){
        $score += $scoreLevel;
        Notifications::addPoints($player,$scoreLevel,clienttranslate('${player_name} scores ${n} points for the difficulty'));
            
        if(! (Globals::isModeDiscovery() && $turn > TURN_MAX)){
          //IN DISCOVERY, if we go far, we don't score actions after turn 10
          $nbActions = $nextActions + $remainingActions;
          $scoreActions = SCORE_PER_ACTION * $nbActions;
          if($scoreActions>0){
            $score += $scoreActions;
            Notifications::addPoints($player,$scoreActions,clienttranslate('${player_name} scores ${n} points for remaining actions : ${n2} remaining turns and ${n3} actions unused in their turn'),$nextTurns,$remainingActions);
          }

          $scoreRecruits = SCORE_PER_RECRUIT*Tokens::countRecruits($pId);
          if($scoreRecruits>0 && !Globals::isModeCompetitive() && !Globals::isModeSoloNoLimit()){
            //In competitive mode this is a tieBreaker only ! (we don't want a player to win thanks to this, but if they are tied OK)
            $score += $scoreRecruits;
            Notifications::addPoints($player,$scoreRecruits,clienttranslate('${player_name} scores ${n} points for remaining tokens in recruit zone'));
          }
          
        }
        if(Globals::isModeSoloNoLimit() && $turn > TURN_MAX){
          //We loose points for each action after Turn Max
          $turnsInExcess = ($turn - TURN_MAX);
          $excess = $turnsInExcess * MAX_PERSONAL_ACTIONS_BY_TURN;
          $score += -$excess;
          Notifications::addPoints($player,-$excess,clienttranslate('${player_name} scores ${n} points for ${n2} turns in excess'),$turnsInExcess);
        }

        $this->computeWinnerTieBreaker($player);
      }
      else {
        $player->setTieBreakerPoints(0);
        $score += SCORE_FAIL;
        Notifications::addPoints($player,SCORE_FAIL,clienttranslate('${player_name} scores ${n} points for not fulfilling the schema'));
      }
      if($player->isJokerUsed() && !Globals::isModeCompetitive()){
        $score += SCORE_JOKER_USED;
        Notifications::addPoints($player,SCORE_JOKER_USED,clienttranslate('${player_name} scores ${n} points for using the joker'));
      }
      $player->addPoints($score);
    }
  }
  

  /**
   * In order :
   * - Joker used
   * - stigmerians in recruit zone
   * - yellow recruits
   * @param Player $player
   */
  public function computeWinnerTieBreaker($player)
  {
    self::trace("computeWinnerTieBreaker()");
    //Init to 0
    $player->setTieBreakerPoints(0);

    $unusedJokers = Globals::getOptionJokers();
    if($player->isJokerUsed()){
      $unusedJokers = 0;
    }
    $player->addTieBreakerPoints( $unusedJokers * TIEBREAKER_FOR_UNUSED_JOKER );
    
    $nbRecruits = Tokens::countRecruits($player->getId());
    if($nbRecruits > 0){
      $player->addTieBreakerPoints( $nbRecruits * TIEBREAKER_FOR_RECRUIT );
    }

    $yellowTokens = Tokens::countRecruits($player->getId(),[TOKEN_STIG_YELLOW]);
    if($yellowTokens > 0){
      $player->addTieBreakerPoints($yellowTokens * TIEBREAKER_FOR_YELLOW_RECRUIT );
    }
  }
  
  public function stPreEndOfGame()
  {
    //Notifications::message(clienttranslate('Game is ending...'));
    $this->gamestate->nextState('next');
  }
}
