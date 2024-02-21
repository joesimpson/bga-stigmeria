<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\PGlobals;
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
            'p_places_p' => $this->listPossiblePlacesOnCentralBoard(),
        ];
        $this->addArgsForUndo($playerId,$args);
        return $args;
    } 
    
    /**
     * @param int $tokenId The token to be moved on central 
     * @param int $row
     * @param int $column
     */
    public function actCJoker($tokenId,$row, $column)
    {
        self::checkAction( 'actCJoker' ); 
        self::trace("actCJoker($tokenId,$row, $column)");
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep( $pId, $player->getPrivateState());
        
        if($player->countRemainingCommonActions() < 1){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        if(!$this->canPlayCentralJoker($player)){
            throw new UnexpectedException(13,"You cannot replay a joker in the game round");
        }
        $token = Tokens::get($tokenId);
        if($token->pId != $pId || $token->location != TOKEN_LOCATION_PLAYER_RECRUIT ){
            throw new UnexpectedException(100,"You cannot select this token");
        }
        $boardTokens = Tokens::getAllOnCentralBoard();
        if(!$this->canPlaceOnCentralBoard($boardTokens,$row, $column)){
            throw new UnexpectedException(30,"You cannot place this token at $row, $column");
        }

        //EFFECT : PLACE the TOKEN 
        //$token->moveToRecruitZoneCentral($player,0);
        $token->moveToCentralBoard($player,$row,$column,0);
        $player->setJokerUsed(true);
        Stats::inc("actions_j",$player->getId());
        Notifications::playCJoker($player,$token);
        $player->incNbCommonActionsDone(1);
        Notifications::useActions($player);
        
        $this->checkGainSpecialAction($player,$token, "next", ST_TURN_COMMON_BOARD);
    }

    public function canPlayCentralJoker($player){
        if(Globals::getOptionJokers() == 0 ) return false;
        if($player->isJokerUsed() ) return false;
        if(Tokens::countRecruits($player->getId()) == 0) return false;
        
        if($player->countRemainingCommonActions() < 1){
            return false;
        }
        return true;
    }
}
