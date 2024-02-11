<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;
use STIG\Managers\Schemas;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait SpecialMergeTrait
{
    public function argSpMerge($player_id)
    {
        $player = Players::get($player_id);
        $mergeableTokens = $this->listMergeableTokens($player_id);
        return [
            'tokens' => $mergeableTokens,
        ];
    } 
    /**
     * Special action of merging 2 tokens of 1 primary color and get 2 of another color
     * @param int $tokenId1
     * @param int $tokenId2
     */
    public function actMerge($tokenId1,$tokenId2)
    {
        self::checkAction( 'actMerge' ); 
        self::trace("actMerge($tokenId1,$tokenId2)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
 
        $actionCost = ACTION_COST_MERGE;
        if($player->countRemainingPersonalActions() < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $token1 = Tokens::get($tokenId1);
        if($token1->pId != $pId || $token1->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(130,"You cannot merge this token");
        }
        $token2 = Tokens::get($tokenId2);
        if($token2->pId != $pId || $token2->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(130,"You cannot merge this token");
        }

        $token1->merge($token2,$player);
        $player->incNbPersonalActionsDone($actionCost);

        $this->gamestate->nextPrivateState($pId, 'next');
    }

    
    /**
     * @param int $playerId
     * @return array List of possible coupled tokens Ids. Example [[ 't1' => 1, 't2' => 5 ],]
     */
    public function listMergeableTokens($playerId){
        $couples = [];
        $boardTokens = Tokens::getAllOnPersonalBoard($playerId);
        foreach($boardTokens as $tokenId1 => $token1){
            $adjacentTokens = Tokens::listAdjacentTokensOnReadBoard($boardTokens,$token1->row, $token1->col);
            foreach($adjacentTokens as $tokenId2 => $token2){
                if($this->canMergeOnBoard($token1,$token2)){
                    $couples[$tokenId1][] = $tokenId2;
                }
            }
        }
        return $couples;
    }
    
    /**
     * @param StigmerianToken $token1
     * @param StigmerianToken $token2
     * @return bool + TRUE if these tokens can be merged on this board
     *  + FALSE otherwise
     */
    public function canMergeOnBoard($token1,$token2){
        if($token1->isPollen()) return false;
        if($token2->isPollen()) return false;
        if(!$token1->isAdjacentToken($token2)) return false; 

        $type1 = $token1->getType();
        $type2 = $token2->getType();
        if($type1 == $type2){
            //cannot merge same colors
            return false; 
        }
        if(array_search($type1, STIG_PRIMARY_COLORS) === FALSE){
            //WE CANNOT MERGE OTHER COLORS
            return false; 
        }
        if(array_search($type2, STIG_PRIMARY_COLORS) === FALSE){
            return false; 
        }

        return true;
    }

 
}
