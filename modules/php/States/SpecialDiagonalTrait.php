<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait SpecialDiagonalTrait
{
    public function argSpDiagonal($player_id)
    {
        $boardTokens = Tokens::getAllOnPersonalBoard($player_id);
        return [
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
        $this->addStep($player->id, $player->getPrivateState());

        $actionType = ACTION_TYPE_DIAGONAL;
        $playerAction = PlayerActions::getPlayer($pId,[$actionType])->first();
        if(!isset($playerAction)){
            throw new UnexpectedException(404,"Not found player action $actionType for $pId");
        }
        if(!$playerAction->canBePlayed($player->countRemainingPersonalActions())){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $actionCost = $playerAction->getCost();
        $token = Tokens::get($token_id);
        if($token->pId != $pId || $token->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(100,"You cannot move this token");
        }
        $boardTokens = Tokens::getAllOnPersonalBoard($pId);
        if(!$this->canMoveDiagonalOnPlayerBoard($pId,$token,$boardTokens,$row, $column)){
            throw new UnexpectedException(101,"You cannot move this token at $row, $column");
        }

        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        $player->giveExtraTime();
        
        //EFFECT : MOVE the TOKEN 
        Notifications::spDiagonal($player,$actionCost);
        $token->moveToPlayerBoard($player,$row,$column,0);
        Stats::inc("actions_s".$actionType,$pId);
        Stats::inc("actions",$player->getId());
        
        PGlobals::setState($player->id, ST_TURN_PERSONAL_BOARD);
        $this->gamestate->nextPrivateState($player->id, "next");
    }

    
    /**
     * @param int $playerId
     * @param StigmerianToken $token
     * @param Collection $boardTokens
     * @param int $row
     * @param int $col
     * @return bool TRUE if this token can be move on this player board ( Empty adjacent spot),
     *  FALSE otherwise
     */
    public function canMoveDiagonalOnPlayerBoard($playerId,$token,$boardTokens,$row, $column)
    {
        if(StigmerianToken::isCoordOutOfGrid($row, $column)) return false;
        if($token->isPollen()) return false;

        if(!$token->isDiagonalAdjacentCoord($row, $column)){
            return false;
        }

        if(null !== (Tokens::findTokenOnBoardWithCoord($boardTokens,$row, $column))) return false;//not empty

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
                    if(isset($playerId) && $this->canMoveDiagonalOnPlayerBoard($playerId,$token,$tokens,$row, $column)){
                        $spots[$tokenId][] = [ 'row' => $row, 'col' => $column ];
                    }
                }
            }
        }
        return $spots;
    }

}
