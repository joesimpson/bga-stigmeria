<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Exceptions\UnexpectedException;
use STIG\Exceptions\UserException;
use STIG\Helpers\Collection;
use STIG\Helpers\Utils;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait PlayerTurnPersonalBoardTrait
{
  
    public function stPersonalBoardTurn()
    {
        self::trace("stCommonBoardTurn()");
        
        Notifications::emptyNotif();
    }

    public function argPersonalBoardTurn($player_id)
    {
        $player = Players::get($player_id);
        $turn = Globals::getTurn();
        $nextPlayer = Players::getNextInactivePlayerInTurn($player->id, $turn);
        
        //TODO JSA LIST POSSIBLE ACTIONS according to Special Actions model
        $actions[] = '';
        if(isset($nextPlayer)){
            $actions[] = 'actLetNextPlay';
        }
        $actions[] = 'actSpecial';
        $possibleJokers = [];
        if(!$player->isJokerUsed()){
            foreach (STIG_PRIMARY_COLORS as $colorSrc) {
                if(!$this->canPlayJoker($player_id,$colorSrc)->isEmpty()){
                    foreach (STIG_PRIMARY_COLORS as $colorDest) {
                        if($colorSrc == $colorDest) continue;
                        $possibleJokers[] = ['src' => $colorSrc, 'dest' => $colorDest] ;
                    }
                }
            }
        }
        return [
            'n'=> $player->countRemainingPersonalActions(),
            'done'=> $player->getNbPersonalActionsDone(),
            'a' => $actions,
            'pj' => $possibleJokers,
        ];
    }
    
    /**
     * TODO JSA : Proof of concept, maybe not necessary
     */
    public function actBackToCommon()
    {
        self::checkAction( 'actBackToCommon' ); 
        
        //moving current player to different state :
        $this->gamestate->nextPrivateState($this->getCurrentPlayerId(), "back");
    }
    
    /**
     * Basic Action 1 : draw a stigmerian in your bag
     */
    public function actDraw()
    {
        self::checkAction( 'actDraw' ); 
        self::trace("actDraw()");
        
        $player = Players::getCurrent();
        $pId = $player->id;

        $remaining = $player->countRemainingPersonalActions();
        $nbActionsDone = $player->getNbPersonalActionsDone();
        $actionCost = 1;//TODO JSA ACTION MODEL ?

        //CHECK REMAINING ACTIONS VS cost
        if($remaining < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        
        $player->setNbPersonalActionsDone($nbActionsDone + $actionCost);
        Notifications::useActions($player);

        $token = Tokens::pickOneForLocation(TOKEN_LOCATION_PLAYER_DECK.$pId, TOKEN_LOCATION_PLAYER_RECRUIT, TOKEN_STATE_STIGMERIAN);
        if($token == null){
            //TODO JSA LOST GAME (maybe already lost before looking in the bag ?)
            throw new UnexpectedException(404,"Not supported draw : empty draw bag for player $pId");
        }

        Notifications::drawToken($player,$token, $actionCost);

        $this->gamestate->nextPrivateState($player->id, "continue");
    }

    
    /**
     * Basic Action 2 : landing a stigmerian on your board
     */
    public function actLand()
    {
        self::checkAction( 'actLand' ); 
        self::trace("actLand()");
        
        $player = Players::getCurrent();

        $remaining = $player->countRemainingPersonalActions();
        $actionCost = 1;

        //CHECK REMAINING ACTIONS VS cost
        if($remaining < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        
        $this->gamestate->nextPrivateState($player->id, "startLand");
    }
    
    /**
     * Basic Action 3 : Moving a stigmerian on your board
     */
    public function actMove()
    {
        self::checkAction( 'actMove' ); 
        self::trace("actMove()");
        
        $player = Players::getCurrent();
        $pId = $player->id;

        $remaining = $player->countRemainingPersonalActions();
        $actionCost = ACTION_COST_MOVE;

        //CHECK REMAINING ACTIONS VS cost
        if($remaining < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }

        $this->gamestate->nextPrivateState($player->id, "startMove");
    }

    /**
     * Joker action
     * @param int $typeSource
     * @param int $typeDest
     */
    public function actJoker($typeSource, $typeDest)
    {
        self::checkAction( 'actJoker' ); 
        self::trace("actJoker($typeSource, $typeDest)");

        $player = Players::getCurrent();
        $pId = $player->id;

        if($player->isJokerUsed()){
            throw new UnexpectedException(13,"You cannot replay a joker in the game round");
        }
        //NORMAL mode joker : 4 same tokens from  recruit zone -> 4 same tokens
        if(array_search($typeDest, STIG_PRIMARY_COLORS) === FALSE){
            throw new UnexpectedException(11,"You cannot play a joker with color $typeDest");
        }
        $tokens = $this->canPlayJoker($pId,$typeSource);
        if($tokens->isEmpty()){
            throw new UnexpectedException(12,"You cannot play a joker");
        }

        //EFFECT
        foreach($tokens as $token){
            $token->setType($typeDest);
        }
        $newTokens = $tokens;
        $player->setJokerUsed(true);
        Notifications::playJoker($player,$typeSource, $typeDest, $newTokens);

        $this->gamestate->nextPrivateState($pId, "continue");
    }
    
    /**
     * Special Action : will go to another state to list available special actions
     */
    public function actSpecial()
    {
        self::checkAction( 'actSpecial' ); 
        self::trace("actSpecial()");
        
        $player = Players::getCurrent();
        $pId = $player->id;

        $this->gamestate->nextPrivateState($player->id, "startSpecial");
    }
    
    /**
     * @return bool + TRUE if a token can be placed on this player board ( Empty spot + Either Line A or adjacent to another token),
     *  + FALSE otherwise
     */
    public function canPlaceOnPlayerBoard($playerId,$row, $column)
    {
        if(StigmerianToken::isCoordOutOfGrid($row, $column)) return false;

        $existingToken = Tokens::findOnPersonalBoard($playerId,$row, $column);
        if(isset($existingToken)) return false;//not empty

        //TODO JSA PERFS We could read all tokens on personal board before calling this function if we want to loop on this func
        if($row != ROW_START && Tokens::listAdjacentTokens($playerId,$row, $column)->isEmpty()){
            return false;
        }

        return true;
    }

    /**
     * @return Collection + (size 4) if 4 tokens of the same type are in player recruit zone,
     *  + (size 0) otherwise
     */
    public function canPlayJoker($playerId, $typeSource){
        $tokens = Tokens::getAllRecruits($playerId)->filter( function($token) use ($typeSource) {
            return $token->type == $typeSource;
        });
        if (count($tokens) >= 4 ){
            return $tokens->limit(4);
        }
        return $tokens->limit(0);
    }

}
