<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Exceptions\UserException;
use STIG\Helpers\Collection;
use STIG\Helpers\Utils;
use STIG\Managers\Players;
use STIG\Managers\Schemas;
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
        
        $actions[] = '';
        if(Tokens::countDeck($player_id) > 0){
            $actions[] = 'actDraw';
        }
        if(Tokens::countRecruits($player_id) > 0){
            $actions[] = 'actLand';
        }
        if(Tokens::countCentralRecruits() > 0 && $player->countRemainingPersonalActions() >= ACTION_COST_CENTRAL_RECRUIT){
            $actions[] = 'actSRecruit';
        }
        if(Tokens::countOnPlayerBoard($player_id, STIG_COLORS ) > 0){
            $actions[] = 'actMove';
        }
        if(isset($nextPlayer)){
            $actions[] = 'actLetNextPlay';
        }
        $actions[] = 'actSpecial';
        $possibleJokers = [];
        if(Globals::getOptionJokers() > 0 && !$player->isJokerUsed()){
            foreach (STIG_PRIMARY_COLORS as $colorSrc) {
                if(!$this->canPlayJoker($player_id,$colorSrc)->isEmpty()){
                    foreach (STIG_PRIMARY_COLORS as $colorDest) {
                        if($colorSrc == $colorDest) continue;
                        $possibleJokers[] = ['src' => $colorSrc, 'dest' => $colorDest] ;
                    }
                }
            }
        }
        $args = [
            'n'=> $player->countRemainingPersonalActions(),
            'done'=> $player->getNbPersonalActionsDone(),
            'a' => $actions,
            'pj' => $possibleJokers,
        ];
        $this->addArgsForUndo($player_id, $args);
        return $args;
    }
    
    /**
     * FOR TESTING only : it is forbidden 
     */
    //public function actBackToCommon()
    //{
    //    self::checkAction( 'actBackToCommon' ); 
    //    
    //    //moving current player to different state :
    //    $this->gamestate->nextPrivateState($this->getCurrentPlayerId(), "back");
    //}
    
    /**
     * Basic Action 1 : draw a stigmerian in your bag
     */
    public function actDraw()
    {
        self::checkAction( 'actDraw' ); 
        self::trace("actDraw()");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep( $player->id, $player->getPrivateState());

        $remaining = $player->countRemainingPersonalActions();
        $actionCost = 1;

        //CHECK REMAINING ACTIONS VS cost
        if($remaining < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        
        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        $player->giveExtraTime();

        $token = Tokens::pickOneForLocation(TOKEN_LOCATION_PLAYER_DECK.$pId, TOKEN_LOCATION_PLAYER_RECRUIT, TOKEN_STATE_STIGMERIAN);
        if($token == null){
            throw new UnexpectedException(404,"Not supported draw : empty draw bag for player $pId");
        }
        Stats::inc("tokens_deck",$player->getId(),-1);
        Stats::inc("tokens_recruit",$player->getId());
        Stats::inc("actions_1",$player->getId());
        Stats::inc("actions",$player->getId());

        Notifications::drawToken($player,$token, $actionCost);

        $this->addCheckpoint(ST_TURN_PERSONAL_BOARD, $player->id);

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
        
        PGlobals::setState($player->id, ST_TURN_CHOICE_TOKEN_LAND);
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

        PGlobals::setState($player->id, ST_TURN_CHOICE_TOKEN_MOVE);
        $this->gamestate->nextPrivateState($player->id, "startMove");
    }

    public function actCancel()
    {
        self::checkAction( 'actCancel' ); 
        self::trace("actCancel()");
        
        $player = Players::getCurrent();
        
        PGlobals::setState($player->id, ST_TURN_PERSONAL_BOARD);
        $this->gamestate->nextPrivateState($player->id, "cancel");
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
        $this->addStep( $player->id, $player->getPrivateState());

        if(Globals::getOptionJokers() == 0 || $player->isJokerUsed()){
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
        Stats::inc("actions_j",$player->getId());
        Notifications::playJoker($player,$typeSource, $typeDest, $newTokens);

        PGlobals::setState($player->id, ST_TURN_PERSONAL_BOARD);
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

        PGlobals::setState($player->id, ST_TURN_CHOICE_SPECIAL_ACTION);
        $this->gamestate->nextPrivateState($player->id, "startSpecial");
    }
    
    /**
     * Recruit on StigmaReine
     */
    public function actSRecruit()
    {
        self::checkAction( 'actSRecruit' ); 
        self::trace("actSRecruit()");
        
        $player = Players::getCurrent();

        PGlobals::setState($player->id, ST_TURN_CHOICE_RECRUIT_CENTRAL);
        $this->gamestate->nextPrivateState($player->id, "sRecruit");
    }
    
    /**
     * @param Collection $boardTokens 
     * @param int $row COORD of new token 
     * @param int $column COORD of new token
     * @param bool $isOnCentralBoard (optional) default false
     * @return bool + TRUE if a token can be placed on this player board ( Empty spot + Either Line A or adjacent to another token),
     *  + FALSE otherwise
     */
    public function canPlaceOnPlayerBoard($boardTokens,$row, $column, $isOnCentralBoard = false)
    {
        if(StigmerianToken::isCoordOutOfGrid($row, $column)) return false;

        $existing = Tokens::findTokenOnBoardWithCoord($boardTokens,$row, $column);
        if(isset($existing)) return false;//not empty spot

        // We can place on LINE A if no tokens are already placed
        //ELSE we must place on adjacent coord
        if( !(  $boardTokens->count() == 0 && (
                $row == ROW_START
                || !$isOnCentralBoard && Globals::isModeNoLimitRules() && (
                    //Free placement on either edge of the board
                    $row == ROW_MAX || $row == ROW_MIN || $column == COLUMN_MAX || $column == COLUMN_MIN
                )
            )
            ||( $boardTokens->count() > 0 
                && Tokens::listAdjacentTokensOnReadBoard($boardTokens,$row, $column)->count() > 0 
             )
            )
        ){
            return false;
        }

        return true;
    }

    /**
     * @param int $playerId
     * @param int $typeSource
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

    /**
     * @param Player $player
     * @return bool + true when every expected tokens of current schema is on player board, and no others !
     *  + false otherwise
     */
    public function isSchemaFulfilled($player){
        self::trace("isSchemaFulfilled()");
        $schema = Schemas::getCurrentSchema();
        $expected = $schema->end;
        $tokens = Tokens::getAllOnPersonalBoard($player->id);
        if ($tokens->count() != count($expected) ){
            return false;
        }
        //We suppose the order of tokens is the same on the 2 collection : (sort by DB on left / statically defined on right)
        //Now let's loop 1 time on tokens to check they match
        $tokenIndex = 0;
        foreach($tokens as $token){
            $expectedToken = $expected->offsetGet($tokenIndex);
            if(!$token->matchesCoord($expectedToken)) return false;
            $tokenIndex++;
        }
        
        return true;
    }
}
