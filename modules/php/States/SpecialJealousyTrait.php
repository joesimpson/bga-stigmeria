<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\Player;
use STIG\Models\StigmerianToken;

trait SpecialJealousyTrait
{
    public function argSpJealousy($player_id)
    {
        $targets = $this->listJealousyTargets($player_id);
        $args = [
            'p' => $targets,
            'cancel' => true,
        ];
        $this->checkCancelFromLastDrift($args,$player_id);
        return $args;
    }  

    /**
     * @param int $from_player_id
     * @return array ids of possible other players
     */
    public function listJealousyTargets($from_player_id){
        return Players::getAll()->filter( function($p) use ($from_player_id) {
            return $p->getId()!= $from_player_id && Tokens::countDeck($p->getId())>0; 
        })->getIds();
    }
    
    /**
     * @param int $targetPlayerId
     */
    public function actSpJealousy($targetPlayerId)
    {
        self::checkAction( 'actSpJealousy' ); 
        self::trace("actSpJealousy($targetPlayerId)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($pId, $player->getPrivateState());

        $actionType = ACTION_TYPE_JEALOUSY;
        $playerAction = PlayerActions::getPlayer($pId,[$actionType])->first();
        if(!isset($playerAction)){
            throw new UnexpectedException(404,"Not found player action $actionType for $pId");
        }
        if(!$playerAction->canBePlayed($player->countRemainingPersonalActions())){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $actionCost = $playerAction->getCost();
        if($targetPlayerId == $pId){
            throw new UnexpectedException(405,"Choose another player ($targetPlayerId)");
        }
        $targetPlayer = Players::get($targetPlayerId);
        $deckSizeTarget = Tokens::countDeck($targetPlayer->getId());
        if($deckSizeTarget <1){
            throw new UnexpectedException(405,"Don't exchange with 0 tokens");
        }
        $deckSize = Tokens::countDeck($player->getId());
        if($deckSize < NB_TOKENS_MIN_JEALOUSY){
            throw new UnexpectedException(405,"Don't exchange with less than 5 tokens");
        }
        //EFFECT : SWAP decks !
        Tokens::swapDecks($player->id,$targetPlayer->id);
        Stats::set("tokens_deck",$player->id,$deckSizeTarget);
        Stats::set("tokens_deck",$targetPlayer->id,$deckSize);
        Notifications::spJealousy($player,$targetPlayer,$deckSizeTarget,$deckSize,$actionCost);

        //This action is now USED IN GAME
        $playerAction->setNewStateAfterUse();
        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player,$playerAction);
        $player->giveExtraTime();
        Stats::inc("actions_s".$actionType,$pId);
        Stats::inc("actions",$pId);
        
        // checkpoint for BOTH PLAYERS in case the other is active !
        if($targetPlayer->isMultiactive()){
            $this->addCheckpoint($targetPlayer->getPrivateState(), $targetPlayer->id );
        }
        if($this->returnToLastDriftState($pId,$playerAction,true)) return;

        PGlobals::setState($player->id, ST_TURN_PERSONAL_BOARD);
        $this->addCheckpoint(ST_TURN_PERSONAL_BOARD,$pId);
        $this->gamestate->nextPrivateState($player->id, "next");
    } 

 
}
