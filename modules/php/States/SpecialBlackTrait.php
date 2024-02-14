<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Helpers\GridUtils;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait SpecialBlackTrait
{
    public function argSpBlack1($player_id)
    {
        $tokens = $this->listBlackableTokens($player_id);
        return [
            'tokens' => $tokens,
        ];
    }
    
    /**
     * Special action of selecting a white token to transform into 2 black tokens
     * @param int $tokenId
     * @param int $row2 COORD of new black token
     * @param int $column2 COORD of new black token
     */
    public function actBlack1($tokenId,$row2, $column2)
    {
        self::checkAction( 'actBlack1' ); 
        self::trace("actBlack1($tokenId)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
 
        $actionCost = ACTION_COST_BLACK;
        if($player->countRemainingPersonalActions() < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $token1 = Tokens::get($tokenId);
        if($token1->pId != $pId || $token1->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(150,"You cannot select this token");
        }
        $existingToken = Tokens::findOnPersonalBoard($pId, $row2,$column2);
        if(!$this->canTurnBlack($token1,$row2, $column2,$existingToken)){
            throw new UnexpectedException(151,"You cannot black this token");
        }

        //EFFECT
        $token2 = Tokens::createToken([
            'type'=>TOKEN_STIG_BLACK,
            'location'=>TOKEN_LOCATION_PLAYER_BOARD,
            'player_id'=>$pId,
            'y'=>$row2,
            'x'=>$column2,
        ]);
        $token1->setType(TOKEN_STIG_BLACK);
        Notifications::spBlack($player,$token1,$token2,$actionCost);
        $token1->checkAndBecomesPollen($player);
        $token2->checkAndBecomesPollen($player);

        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        Stats::inc("actions_s8",$pId);
        Stats::inc("actions",$pId);
        Stats::inc("tokens_board",$pId,+1);

        $this->gamestate->nextPrivateState($pId, 'next');
    }

    ///**
    // * Special action of selecting a white token to transform into 2 black tokens
    // * -> STEP 1 : white selection
    // * @param int $tokenId
    // */
    //public function actBlack1($tokenId,$row2, $column2)
    //{
    //    self::checkAction( 'actBlack1' ); 
    //    self::trace("actBlack1($tokenId)");
    //    
    //    $player = Players::getCurrent();
    //    $pId = $player->id;
 //
    //    $actionCost = ACTION_COST_BLACK;
    //    if($player->countRemainingPersonalActions() < $actionCost){
    //        throw new UnexpectedException(10,"Not enough actions to do that");
    //    }
    //    $token1 = Tokens::get($tokenId);
    //    if($token1->pId != $pId || $token1->location != TOKEN_LOCATION_PLAYER_BOARD ){
    //        throw new UnexpectedException(150,"You cannot select this token");
    //    }
    //    if(!$this->canTurnBlack($token1)){
    //        throw new UnexpectedException(151,"You cannot black this token");
    //    }
//
    //    Globals::setSelectedTokens([$tokenId]); 
    //    $this->gamestate->nextPrivateState($pId, 'next');
    //}
//
    ///**
    // * Special action of selecting a white token to transform into 2 black tokens
    // * -> STEP 2 : black selection
    // * @param int $row2 COORD of new black token
    // * @param int $column2 COORD of new black token
    // */
    //public function actBlack2($row2, $column2)
    //{
    //    self::checkAction( 'actBlack2' ); 
    //    self::trace("actBlack2($row2, $column2)");
    //    
    //    $player = Players::getCurrent();
    //    $pId = $player->id;
 //
    //    $actionCost = ACTION_COST_BLACK;
    //    if($player->countRemainingPersonalActions() < $actionCost){
    //        throw new UnexpectedException(10,"Not enough actions to do that");
    //    }
    //    $selectedTokens = Globals::getSelectedTokens();
    //    if(count($selectedTokens) != 1) {
    //        throw new UnexpectedException(132,"Wrong selection");
    //    }
    //    $token1 = Tokens::get($selectedTokens[0]);
    //    if(!$this->canTurnBlack($token1,$row2, $column2)){
    //        throw new UnexpectedException(152,"You cannot black this token here");
    //    }
    //    
    //    //TODO JSA EFFECT
//
    //    $this->gamestate->nextPrivateState($pId, 'next');
    //}
    
    /**
     * @param int $playerId
     * @return array List of possible spaces. Example [[ 'row' => 1, 'col' => 5 ],]
     */
    public function listBlackableTokens($playerId){
        $spots = [];
        $boardTokens = Tokens::getAllOnPersonalBoard($playerId);
        foreach($boardTokens as $tokenId1 => $token1){
            $pos = ['x' => $token1->col, 'y' => $token1->row];
            $adjacentSpaces = GridUtils::getNeighbours($pos);
            foreach($adjacentSpaces as $adjacentSpace){
                $row = $adjacentSpace['y'];
                $col = $adjacentSpace['x'];
                $existingToken = Tokens::findTokenOnBoardWithCoord($boardTokens,$row, $col);
                if($this->canTurnBlack($token1,$row,$col,$existingToken)){
                    $spots[$tokenId1][] = [ 'row' => $row, 'col' => $col ];
                }
            }
        }
        return $spots;
    }
    
    /**
     * @param StigmerianToken $token1
     * @param int $row2 COORD of new black token
     * @param int $column2 COORD of new black token
     * @param bool $existingToken 
     * @return bool + TRUE if this tokens can be turned to 2 black tokens
     *  + FALSE otherwise
     */
    public function canTurnBlack($token1,$row2, $column2,$existingToken){
        if($token1->getType() != TOKEN_STIG_WHITE ) return false; 
        if(StigmerianToken::isCoordOutOfGrid($row2, $column2)) return false; 
        if(!$token1->isAdjacentCoord($row2, $column2)) return false; 
        if(isset($existingToken)) return false;

        return true;
    }

 
}
