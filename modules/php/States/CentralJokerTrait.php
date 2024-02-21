<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;
use STIG\Managers\Tokens;

trait CentralJokerTrait
{
    public function argCJoker($playerId)
    {
        //TODO JSA we could filter and keep only 1 per different color
        $tokens = Tokens::getAllRecruits($playerId);
        $args = [
            'tokens'=> $tokens->ui(),
        ];
        $this->addArgsForUndo($playerId,$args);
        return $args;
    } 
    
    /**
     * @param int $tokenId The token to be moved on central recruit zone
     */
    public function actCJoker($tokenId)
    {
        self::checkAction( 'actCJoker' ); 
        self::trace("actCJoker($tokenId)");
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep( $pId, $player->getPrivateState());
        
        if(!$this->canPlayCentralJoker($player)){
            throw new UnexpectedException(13,"You cannot replay a joker in the game round");
        }
        $token = Tokens::get($tokenId);
        if($token->pId != $pId || $token->location != TOKEN_LOCATION_PLAYER_RECRUIT ){
            throw new UnexpectedException(100,"You cannot select this token");
        }
        $token->moveToRecruitZoneCentral($player,0);
        $player->setJokerUsed(true);
        Stats::inc("actions_j",$player->getId());
        Notifications::playCJoker($player,$token);
        $this->gamestate->nextPrivateState($player->id, "next");
    }

    public function canPlayCentralJoker($player){
        if(Globals::getOptionJokers() == 0 ) return false;
        if($player->isJokerUsed() ) return false;
        if(Tokens::countRecruits($player->getId()) == 0) return false;
        return true;
    }
}
