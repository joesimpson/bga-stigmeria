<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;
use STIG\Managers\Schemas;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

/**
 * Function related to using special Action 'Mixing', 
 */
trait SpecialMixingTrait
{
    public function argSpMixing($player_id)
    {
        $mixableTokens = $this->listMixableTokens($player_id);
        return [
            'tokens' => $mixableTokens,
        ];
    } 
    /**
     * Special action of Mixing 2 tokens of 1 primary color and get 2 of another color
     * @param int $tokenId1
     * @param int $tokenId2
     */
    public function actMixing($tokenId1,$tokenId2)
    {
        self::checkAction( 'actMixing' ); 
        self::trace("actMixing($tokenId1,$tokenId2)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
 
        $actionCost = ACTION_COST_MIXING* $this->getGetActionCostModifier();
        if($player->countRemainingPersonalActions() < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $token1 = Tokens::get($tokenId1);
        if($token1->pId != $pId || $token1->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(130,"You cannot Mix this token");
        }
        $token2 = Tokens::get($tokenId2);
        if($token2->pId != $pId || $token2->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(130,"You cannot Mix this token");
        }
        if(!$this->canMixOnBoard($token1,$token2)){
            throw new UnexpectedException(131,"You cannot Mix these tokens");
        }

        $token1->mix($token2,$player,$actionCost);
        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        $player->giveExtraTime();
        Stats::inc("actions_s".ACTION_TYPE_MIXING,$pId);
        Stats::inc("actions",$player->getId());

        $this->gamestate->nextPrivateState($pId, 'next');
    }

    
    /**
     * @param int $playerId
     * @return array List of possible coupled tokens Ids. Example [[ 't1' => 1, 't2' => 5 ],]
     */
    public function listMixableTokens($playerId){
        $couples = [];
        $boardTokens = Tokens::getAllOnPersonalBoard($playerId);
        foreach($boardTokens as $tokenId1 => $token1){
            $adjacentTokens = Tokens::listAdjacentTokensOnReadBoard($boardTokens,$token1->row, $token1->col);
            foreach($adjacentTokens as $tokenId2 => $token2){
                if($this->canMixOnBoard($token1,$token2)){
                    $couples[$tokenId1][] = $tokenId2;
                }
            }
        }
        return $couples;
    }
    
    /**
     * @param StigmerianToken $token1
     * @param StigmerianToken $token2
     * @return bool + TRUE if these tokens can be Mixed on this board
     *  + FALSE otherwise
     */
    public function canMixOnBoard($token1,$token2){
        if($token1->isPollen()) return false;
        if($token2->isPollen()) return false;
        if(!$token1->isAdjacentToken($token2)) return false; 

        $type1 = $token1->getType();
        $type2 = $token2->getType();
        if($type1 == $type2){
            //cannot Mix same colors
            return false; 
        }
        if(array_search($type1, STIG_PRIMARY_COLORS) === FALSE){
            //WE CANNOT MIX OTHER COLORS
            return false; 
        }
        if(array_search($type2, STIG_PRIMARY_COLORS) === FALSE){
            return false; 
        }

        return true;
    }

 
}
