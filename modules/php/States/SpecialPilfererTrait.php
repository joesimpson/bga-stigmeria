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

/**
* "Chapardeur" in French
*/

trait SpecialPilfererTrait
{
    public function argSpPilferer($player_id)
    {
        $targets = $this->listPilfererTargets($player_id);
        return [
            'p' => $targets,
        ];
    }  

    /**
     * @param int $from_player_id
     * @return array ids of possible other players
     */
    public function listPilfererTargets($from_player_id){
        return Players::getAll()->filter( function($p) use ($from_player_id) {
            return $p->getId()!= $from_player_id && Tokens::countDeck($p->getId())>0; 
        })->getIds();
    }
    
    /**
     * @param int $targetPlayerId
     */
    public function actPilferer($targetPlayerId)
    {
        self::checkAction( 'actPilferer' ); 
        self::trace("actPilferer($targetPlayerId)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($pId, $player->getPrivateState());

        $actionType = ACTION_TYPE_PILFERER;
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
        $token = Tokens::getTopOf(TOKEN_LOCATION_PLAYER_DECK.$targetPlayer->id);
        if($token == null){
            throw new UnexpectedException(404,"No more tokens to draw from targeted player bag ($targetPlayerId)");
        }
        //EFFECT : MOVE to RECRUIT
        $token->moveToRecruitZone($player,0);
        Stats::inc("tokens_deck",$targetPlayer->id,-1);
        Notifications::spPilferer($player,$targetPlayer,$token,$actionCost);

        //This action is now USED IN player turn
        $playerAction->setNewStateAfterUse();
        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player,$playerAction);
        $player->giveExtraTime();
        Stats::inc("actions_s".$actionType,$pId);
        Stats::inc("actions",$pId);
        
        PGlobals::setState($player->id, ST_TURN_PERSONAL_BOARD);
        // checkpoint for BOTH PLAYERS in case the other is active !
        $this->addCheckpoint(ST_TURN_PERSONAL_BOARD,$pId);
        if($targetPlayer->isMultiactive()){
            $this->addCheckpoint($targetPlayer->getPrivateState(), $targetPlayer->id );
        }
        $this->gamestate->nextPrivateState($player->id, "next");
    } 

 
}
