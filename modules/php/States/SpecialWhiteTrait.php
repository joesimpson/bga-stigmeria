<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait SpecialWhiteTrait
{
    public function argSpWhite($player_id)
    {
        $tokens = $this->listWhiteableTokens($player_id);
        return [
            'tokens' => $tokens,
        ];
    } 
    public function argSpWhiteChoice($player_id)
    {
        $player = Players::get($player_id);
        $selectedTokens = $player->getSelection();
        return [
            'tokensIds' => $selectedTokens,
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
 
        $actionType = ACTION_TYPE_WHITE;
        $playerAction = PlayerActions::getPlayer($pId,[$actionType])->first();
        if(!isset($playerAction)){
            throw new UnexpectedException(404,"Not found player action $actionType for $pId");
        }
        if(!$playerAction->canBePlayed($player->countRemainingPersonalActions())){
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
        $player->setSelection([$tokenId1, $tokenId2]);
        PGlobals::setState($player->id, ST_TURN_SPECIAL_ACT_WHITE_STEP2);
        $this->gamestate->nextPrivateState($pId, 'next');
    }
    
    /**
     * Special action of merging 2 adjacent black tokens into a white one - choice of white space
     * @param int $tokenId
     */
    public function actWhiteChoice($tokenId)
    {
        self::checkAction( 'actWhiteChoice' ); 
        self::trace("actWhiteChoice($tokenId)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($player->id, $player->getPrivateState());
 
        $actionType = ACTION_TYPE_WHITE;
        $playerAction = PlayerActions::getPlayer($pId,[$actionType])->first();
        if(!isset($playerAction)){
            throw new UnexpectedException(404,"Not found player action $actionType for $pId");
        }
        if(!$playerAction->canBePlayed($player->countRemainingPersonalActions())){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $actionCost = $playerAction->getCost();
        $selectedTokens = $player->getSelection();
        if(count($selectedTokens) != 2) {
            throw new UnexpectedException(132,"Wrong selection");
        }
        if($selectedTokens[0] !=$tokenId && $selectedTokens[1] !=$tokenId){
            throw new UnexpectedException(132,"Wrong selection");
        }
        $token1 = Tokens::get($selectedTokens[0]);
        $token2 = Tokens::get($selectedTokens[1]);
        if($token1->pId != $pId || $token1->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(150,"You cannot select this token");
        }
        if($token2->pId != $pId || $token2->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(150,"You cannot select this token");
        }
        if(!$this->canWhiteOnBoard($token1, $token2)){
            throw new UnexpectedException(131,"You cannot white these tokens");
        }

        //  EFFECT
        if($token1->id ==$tokenId ){
            $token1->setType(TOKEN_STIG_WHITE);
            Notifications::spWhite($player,$token1,$token2,$actionCost); 
            Tokens::delete($token2->id);
            $token1->checkAndBecomesPollen($player);
        }
        else if($token2->id ==$tokenId ){
            $token2->setType(TOKEN_STIG_WHITE);
            Notifications::spWhite($player,$token2,$token1,$actionCost); 
            Tokens::delete($token1->id);
            $token2->checkAndBecomesPollen($player);
        }
        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        $player->giveExtraTime();
        Stats::inc("actions_s".$actionType,$pId);
        Stats::inc("actions",$player->getId());
        Stats::inc("tokens_board",$pId,-1);
        $player->setSelection([]);

        PGlobals::setState($player->id, ST_TURN_PERSONAL_BOARD);
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
