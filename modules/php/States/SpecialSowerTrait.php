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
* "Semeur" in French
*/

trait SpecialSowerTrait
{
    public function argSpSower($player_id)
    {
        $colors = STIG_COLORS;
        return [
            'colors' => $colors,
        ];
    }  
    
    /**
     * @param int $targetPlayerId
     */
    public function actSower($targetPlayerId,$typeDest)
    {
        self::checkAction( 'actSower' ); 
        self::trace("actSower($targetPlayerId)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($pId, $player->getPrivateState());

        $actionType = ACTION_TYPE_SOWER;
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
        if(!in_array($typeDest, STIG_COLORS)){
            throw new UnexpectedException(11,"You cannot select a dest color $typeDest");
        }
        $targetPlayer = Players::get($targetPlayerId);
        //EFFECT : ADD TOKEN
        $token = Tokens::createToken([
            'type' => $typeDest,
            'location' => TOKEN_LOCATION_PLAYER_DECK.$targetPlayerId,
            'player_id' => $targetPlayerId,
        ]);
        Stats::inc("tokens_deck",$targetPlayer->id,+1);
        Notifications::spSower($player,$targetPlayer,$token,$actionCost);
        Tokens::shuffleBag($targetPlayerId);

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
