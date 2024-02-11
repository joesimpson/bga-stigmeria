<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait CentralMoveTrait
{
    public function argCentralChoiceTokenToMove($player_id)
    {
        //$player = Players::get($player_id);
        $boardTokens = Tokens::getAllOnCentralBoard();
        return [
            'n' => ACTION_COST_CENTRAL_MOVE,
            'p_places_m' => $this->listPossibleMovesOnBoard(null,$boardTokens),
        ];
    }
      
    /**
     * Central Action 2 : Moving a stigmerian on central board
     * @param int $token_id
     * @param int $row
     * @param int $column
     */
    public function actCentralMove($token_id, $row, $column)
    {
        self::checkAction( 'actCentralMove' ); 
        self::trace("actCentralMove($token_id, $row, $column)");
        
        $player = Players::getCurrent();
        $pId = $player->id;

        if($player->isCommonMoveDone()){
            throw new UnexpectedException(9,"You cannot do that action twice in the turn");
        }
        $remaining = $player->countRemainingCommonActions();
        $actionCost = ACTION_COST_CENTRAL_MOVE;
        if($remaining < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $token = Tokens::get($token_id);
        if($token->location != TOKEN_LOCATION_CENTRAL_BOARD ){
            throw new UnexpectedException(20,"You cannot move this token");
        }
        if(!$this->canMoveOnCentralBoard($token,$row, $column)){
            throw new UnexpectedException(30,"You cannot move this token at $row, $column");
        }

        $player->incNbCommonActionsDone($actionCost);
        $player->setCommonMoveDone(true);
        Notifications::useActions($player);
        Stats::inc("actions_c2",$player->getId());

        //EFFECT : PLACE the TOKEN 
        $token->moveToCentralBoard($player,$row,$column,$actionCost);
        //TODO JSA RULE : gain 1 special action

        $this->gamestate->nextPrivateState($player->id, "continue");
    }
     
    /**
     * @param StigmerianToken $token
     * @param int $row
     * @param int $column
     * @return bool TRUE if this token can be moved on central board ( Empty spot + Either Line A or adjacent to another token),
     *  FALSE otherwise
     */
    public function canMoveOnCentralBoard($token,$row, $column)
    {
        if(StigmerianToken::isCoordOutOfGrid($row, $column)) return false;
        if(Tokens::countOnCentralBoard($row, $column) > 0) return false;//not empty

        if(!$token->isAdjacentCoord($row, $column)){
            return false;
        }

        return true;
    }
}
