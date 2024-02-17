<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait ChoiceTokenDiagonalTrait
{
    public function argSpDiagonal($player_id)
    {
        $player = Players::get($player_id);
        $boardTokens = Tokens::getAllOnPersonalBoard($player_id);
        return [
            'n' => ACTION_COST_MOVE_DIAGONAL,
            'p_places_m' => $this->listPossibleDiagonalMovesOnBoard($player_id,$boardTokens),
        ];
    }
      
    /**
     * @param int $token_id
     * @param int $row
     * @param int $col
     */
    public function actDiagonal($token_id, $row, $column)
    {
        self::checkAction( 'actDiagonal' ); 
        self::trace("actDiagonal($token_id, $row, $column)");
        
        $player = Players::getCurrent();
        $pId = $player->id;

        $remaining = $player->countRemainingPersonalActions();
        $actionCost = ACTION_COST_MOVE_DIAGONAL* $this->getGetActionCostModifier();

        //CHECK REMAINING ACTIONS VS cost
        if($remaining < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $token = Tokens::get($token_id);
        if($token->pId != $pId || $token->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(100,"You cannot move this token");
        }
        if(!$this->canMoveDiagonalOnPlayerBoard($pId,$token,$row, $column)){
            throw new UnexpectedException(101,"You cannot move this token at $row, $column");
        }

        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        
        //EFFECT : MOVE the TOKEN 
        $token->moveToPlayerBoard($player,$row,$column,$actionCost);
        Stats::inc("actions_s".ACTION_TYPE_DIAGONAL,$pId);
        Stats::inc("actions",$player->getId());
        
        $this->gamestate->nextPrivateState($player->id, "next");
    }

    
    /**
     * @param int $playerId
     * @param StigmerianToken $token
     * @param int $row
     * @param int $col
     * @return bool TRUE if this token can be move on this player board ( Empty adjacent spot),
     *  FALSE otherwise
     */
    public function canMoveDiagonalOnPlayerBoard($playerId,$token,$row, $column)
    {
        if(StigmerianToken::isCoordOutOfGrid($row, $column)) return false;
        if($token->isPollen()) return false;

        if(!$token->isDiagonalAdjacentCoord($row, $column)){
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
    public function listPossibleDiagonalMovesOnBoard($playerId,$tokens){
        $spots = [];
        foreach($tokens as $tokenId => $token){
            for($row = ROW_MIN; $row <=ROW_MAX; $row++ ){
                for($column = COLUMN_MIN; $column <=COLUMN_MAX; $column++ ){
                    if(isset($playerId) && $this->canMoveDiagonalOnPlayerBoard($playerId,$token,$row, $column)){
                        $spots[$tokenId][] = [ 'row' => $row, 'col' => $column ];
                    }
                }
            }
        }
        return $spots;
    }

}
