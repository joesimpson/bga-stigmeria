<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;
use STIG\Managers\Schemas;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait SpecialSwapTrait
{
    public function argSpSwap($player_id)
    {
        $player = Players::get($player_id);
        $tokens = $this->listSwapableTokens($player_id);
        return [
            'tokens' => $tokens,
        ];
    } 
    /**
     * Special action of swaping 2 adjacent tokens 
     * @param int $tokenId1
     * @param int $tokenId2
     */
    public function actSwap($tokenId1,$tokenId2)
    {
        self::checkAction( 'actSwap' ); 
        self::trace("actSwap($tokenId1,$tokenId2)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
 
        $actionCost = ACTION_COST_SWAP* $this->getGetActionCostModifier();
        if($player->countRemainingPersonalActions() < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $token1 = Tokens::get($tokenId1);
        if($token1->pId != $pId || $token1->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(130,"You cannot swap this token");
        }
        $token2 = Tokens::get($tokenId2);
        if($token2->pId != $pId || $token2->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(130,"You cannot swap this token");
        }
        if(!$this->canSwapOnBoard($token1,$token2)){
            throw new UnexpectedException(131,"You cannot swap these tokens");
        }

        //SWAP EFFECT
        $previousCoord1 = $token1->asCoord();
        $previousCoord2 = $token2->asCoord();
        $token1->moveToPlayerBoard($player,$previousCoord2->row,$previousCoord2->col,0,false);
        $token2->moveToPlayerBoard($player,$previousCoord1->row,$previousCoord1->col,0,false);
        Notifications::spSwap($player,$token1,$token2,$actionCost);

        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        $player->giveExtraTime();
        Stats::inc("actions_s".ACTION_TYPE_SWAP,$pId);
        Stats::inc("actions",$player->getId());

        $this->gamestate->nextPrivateState($pId, 'next');
    }

    
    /**
     * @param int $playerId
     * @return array List of possible coupled tokens Ids. Example [[ 't1' => 1, 't2' => 5 ],]
     */
    public function listSwapableTokens($playerId){
        $couples = [];
        $boardTokens = Tokens::getAllOnPersonalBoard($playerId);
        foreach($boardTokens as $tokenId1 => $token1){
            $adjacentTokens = Tokens::listAdjacentTokensOnReadBoard($boardTokens,$token1->row, $token1->col);
            foreach($adjacentTokens as $tokenId2 => $token2){
                if($this->canSwapOnBoard($token1,$token2)){
                    $couples[$tokenId1][] = $tokenId2;
                }
            }
        }
        return $couples;
    }
    
    /**
     * @param StigmerianToken $token1
     * @param StigmerianToken $token2
     * @return bool + TRUE if these tokens can be swapped on this board
     *  + FALSE otherwise
     */
    public function canSwapOnBoard($token1,$token2){
        if($token1->isPollen()) return false;
        if($token2->isPollen()) return false;
        if(!$token1->isAdjacentToken($token2)) return false; 

        return true;
    }

 
}
