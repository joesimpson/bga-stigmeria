<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait ChoiceTokenToMoveTrait
{
    public function argChoiceTokenToMove($player_id)
    {
        $player = Players::get($player_id);
        $boardTokens = Tokens::getAllOnPersonalBoard($player_id);
        return [
            'n' => ACTION_COST_MOVE,
            'p_places_m' => $this->listPossibleMovesOnPersonalBoard($player_id,$boardTokens),
        ];
    }
      
    public function actCancelChoiceTokenToMove()
    {
        self::checkAction( 'actCancelChoiceTokenToMove' ); 
        self::trace("actCancelChoiceTokenToMove()");
        
        $player = Players::getCurrent();

        //NOTHING TO CANCEL In BDD, return to previous state

        $this->gamestate->nextPrivateState($player->id, "cancel");
    }
    /**
     * @param int $token_id
     * @param int $row
     * @param int $col
     */
    public function actChoiceTokenToMove($token_id, $row, $column)
    {
        self::checkAction( 'actChoiceTokenToMove' ); 
        self::trace("actChoiceTokenToMove($token_id, $row, $column)");
        
        $player = Players::getCurrent();
        $pId = $player->id;

        $remaining = $player->countRemainingPersonalActions();
        $actionCost = ACTION_COST_MOVE;

        //CHECK REMAINING ACTIONS VS cost
        if($remaining < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $token = Tokens::get($token_id);
        if($token->pId != $pId || $token->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(100,"You cannot move this token");
        }
        if(!$this->canMoveOnPlayerBoard($pId,$token,$row, $column)){
            throw new UnexpectedException(101,"You cannot move this token at $row, $column");
        }

        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);

        //EFFECT : MOVE the TOKEN 
        $token->moveToPlayerBoard($player,$row,$column,$actionCost);

        $this->gamestate->nextPrivateState($player->id, "continue");
    }

    
    /**
     * @param int $playerId
     * @param StigmerianToken $token
     * @param int $row
     * @param int $col
     * @return bool TRUE if a token can be move on this player board ( Empty adjacent spot),
     *  FALSE otherwise
     */
    public function canMoveOnPlayerBoard($playerId,$token,$row, $column)
    {
        //TODO JSA RULE MANAGE EXITING TOKEN is possible, by another button 
        if(StigmerianToken::isCoordOutOfGrid($row, $column)) return false;
        if($token->isPollen()) return false;

        if(!$token->isAdjacentCoord($row, $column)){
            return false;
        }

        //TODO JSA PERFS We could read all tokens on personal board before calling this function if we want to loop on this func
        $existingToken = Tokens::findOnPersonalBoard($playerId,$row, $column);
        if(isset($existingToken)) return false;//not empty

        return true;
    }

    /**
     * @param int $playerId
     * @param array $tokens of StigmerianToken
     * @return array List of possible spaces. Example [[ 'row' => 1, 'col' => 5 ],]
     */
    public function listPossibleMovesOnPersonalBoard($playerId,$tokens){
        $spots = [];
        foreach($tokens as $tokenId => $token){
            for($row = ROW_MIN; $row <=ROW_MAX; $row++ ){
                for($column = COLUMN_MIN; $column <=COLUMN_MAX; $column++ ){
                    if($this->canMoveOnPlayerBoard($playerId,$token,$row, $column)){
                        $spots[$tokenId][] = [ 'row' => $row, 'col' => $column ];
                    }
                }
            }
        }
        return $spots;
    }

}
