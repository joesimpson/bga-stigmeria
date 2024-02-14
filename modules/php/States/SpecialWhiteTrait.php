<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait SpecialWhiteTrait
{
    public function argSpWhite($player_id)
    {
        $player = Players::get($player_id);
        $tokens = $this->listWhiteableTokens($player_id);
        return [
            'tokens' => $tokens,
        ];
    } 
    /**
     * Special action of merging 2 adjacent black tokens into a white one
     * @param int $tokenId1
     * @param int $tokenId2
     */
    public function actWhite($tokenId1,$tokenId2)
    {
        self::checkAction( 'actWhite' ); 
        self::trace("actWhite($tokenId1,$tokenId2)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
 
        $actionCost = ACTION_COST_WHITE;
        if($player->countRemainingPersonalActions() < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $token1 = Tokens::get($tokenId1);
        if($token1->pId != $pId || $token1->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(130,"You cannot white this token");
        }
        $token2 = Tokens::get($tokenId2);
        if($token2->pId != $pId || $token2->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(130,"You cannot white this token");
        }
        if(!$this->canWhiteOnBoard($token1,$token2)){
            throw new UnexpectedException(131,"You cannot white these tokens");
        }

        //TODO JSA  EFFECT
        Notifications::swapTokens($player,$token1,$token2,$actionCost); 

        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        Stats::inc("actions_s6",$player->getId());
        Stats::inc("actions",$player->getId());

        $this->gamestate->nextPrivateState($pId, 'next');
    }

    
    /**
     * @param int $playerId
     * @return array List of possible coupled tokens Ids. Example [[ 't1' => 1, 't2' => 5 ],]
     */
    public function listWhiteableTokens($playerId){
        $couples = [];
        $boardTokens = Tokens::getAllOnPersonalBoard($playerId);
        foreach($boardTokens as $tokenId1 => $token1){
            $adjacentTokens = Tokens::listAdjacentTokensOnReadBoard($boardTokens,$token1->row, $token1->col);
            foreach($adjacentTokens as $tokenId2 => $token2){
                if($this->canWhiteOnBoard($token1,$token2)){
                    $couples[$tokenId1][] = $tokenId2;
                }
            }
        }
        return $couples;
    }
    
    /**
     * @param StigmerianToken $token1
     * @param StigmerianToken $token2
     * @return bool + TRUE if these tokens can be merged As a white token on this board
     *  + FALSE otherwise
     */
    public function canWhiteOnBoard($token1,$token2){
        if($token1->getType() != TOKEN_STIG_BLACK ) return false; 
        if($token2->getType() != TOKEN_STIG_BLACK ) return false; 
        if(!$token1->isAdjacentToken($token2)) return false; 

        return true;
    }

 
}
