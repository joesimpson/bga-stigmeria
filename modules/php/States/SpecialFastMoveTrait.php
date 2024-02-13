<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;
use STIG\Managers\Schemas;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;
use STIG\Models\TokenCoord;

trait SpecialFastMoveTrait
{
    public function argSpFastMove($player_id)
    {
        $player = Players::get($player_id);
        $boardTokens = Tokens::getAllOnPersonalBoard($player_id);
        $nMoves = Globals::getTurn();
        $possibleMoves = [];
        //TODO JSA PERFS FAST MOVE
        //$possibleMoves = $this->listPossibleFastMovesOnBoard($player_id,$boardTokens,$nMoves);
        $possibleMoves = $this->listPossibleFastMovesOnBoard($player_id,$boardTokens,5);
        return [
            'n' => $nMoves,
            'p_places_m' => $possibleMoves,
        ];
    } 
    /**
     * Special action of moving 1 token with 1->N moves to somewhere
     * @param int $token_id
     * @param int $row
     * @param int $col
     */
    public function actFastMove($token_id, $row, $column)
    {
        self::checkAction( 'actFastMove' ); 
        self::trace("actFastMove($token_id, $row, $column)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $turn = Globals::getTurn();
 
        $actionCost = ACTION_COST_MOVE_FAST;
        if($player->countRemainingPersonalActions() < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $token = Tokens::get($token_id);
        if($token->pId != $pId || $token->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(100,"You cannot move this token");
        }
        $boardTokens = Tokens::getAllOnPersonalBoard($pId);
        if(!$this->canMoveFastOnPlayerBoard($pId,$boardTokens,$token,$row, $column, $turn)){
            throw new UnexpectedException(101,"You cannot move this token at $row, $column");
        }
        //TODO JSA CHECK NOT USED IN player turn

        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        //EFFECT : MOVE the TOKEN 
        $token->moveToPlayerBoard($player,$row,$column,$actionCost);
        Stats::inc("actions_s6",$player->getId());
        Stats::inc("actions",$player->getId());

        $this->gamestate->nextPrivateState($pId, 'next');
    }

    /**
     * @param int $playerId
     * @param Collection $boardTokens of StigmerianToken
     * @param StigmerianToken $token
     * @param int $row
     * @param int $column
     * @param int $nMoves number of steps to move
     * @return bool TRUE if this token can be move on this player board ( Empty adjacent spots on path) with n steps,
     *  FALSE otherwise
     */
    public function canMoveFastOnPlayerBoard($playerId,$boardTokens,$token,$row, $column,$nMoves)
    {
        if(StigmerianToken::isCoordOutOfGrid($row, $column)) return false;
        if($token->isPollen()) return false;

        $existingToken = Tokens::findTokenOnBoardWithCoord($boardTokens,$row, $column);
        if(isset($existingToken)) return false;//not empty

        if($token->isAdjacentCoord($row,$column)) return true;// GOOD path !

        if($nMoves >1){
            $nMoves = min( $nMoves, (int) MAX_MOVES_TO_REACH_A_PLACE);
            //self::trace("actFastMove() nMoves =$nMoves //");
            //RECURSIVE CALL on adjacent tokens
            $neighboursCoord = TokenCoord::listAdjacentCoords($row, $column);
            foreach($neighboursCoord as $neighbour){
                if($this->canMoveFastOnPlayerBoard($playerId,$boardTokens,$token,$neighbour->row, $neighbour->col,$nMoves-1)){
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * @param int $playerId
     * @param Collection $tokens of StigmerianToken
     * @return array List of possible spaces. Example [[ 'row' => 1, 'col' => 5 ],]
     */
    public function listPossibleFastMovesOnBoard($playerId,$tokens, $nMoves){
        self::trace("listPossibleFastMovesOnBoard($playerId, $nMoves)");
        $spots = [];
        foreach($tokens as $tokenId => $token){
            for($row = ROW_MIN; $row <=ROW_MAX; $row++ ){
                for($column = COLUMN_MIN; $column <=COLUMN_MAX; $column++ ){
                    if($this->canMoveFastOnPlayerBoard($playerId,$tokens,$token,$row, $column, $nMoves)){
                        $spots[$tokenId][] = [ 'row' => $row, 'col' => $column ];
                    }
                }
            }
        }
        return $spots;
    }

 
}
