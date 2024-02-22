<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait SpecialTwoBeatsTrait
{
    public function argSpTwoBeats($player_id)
    {
        $spots = $this->listSpotsForTwoBeats($player_id);
        return [
            'p_places_p' => $spots,
        ];
    }
    
    /**
     * Special action of selecting a position for a white token, with 8 neighbours
     * @param int $row COORD of new white token
     * @param int $column COORD of new white token
     */
    public function actTwoBeats($row, $column)
    {
        self::checkAction( 'actTwoBeats' ); 
        self::trace("actTwoBeats($row, $column)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($player->id, $player->getPrivateState());
 
        $actionType = ACTION_TYPE_TWOBEATS;
        $playerAction = PlayerActions::getPlayer($pId,[$actionType])->first();
        if(!isset($playerAction)){
            throw new UnexpectedException(404,"Not found player action $actionType for $pId");
        }
        if(!$playerAction->canBePlayed($player->countRemainingPersonalActions())){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $actionCost = $playerAction->getCost();
        $boardTokens = Tokens::getAllOnPersonalBoard($pId);
        if(!$this->canPlayTwoBeats($row, $column,$boardTokens)){
            throw new UnexpectedException(153,"You cannot Two beats there");
        }

        //EFFECT
        $token = Tokens::createToken([
            'type'=>TOKEN_STIG_WHITE,
            'location'=>TOKEN_LOCATION_PLAYER_BOARD,
            'player_id'=>$pId,
            'y'=>$row,
            'x'=>$column,
        ]);
        Notifications::spTwoBeats($player,$token,$actionCost);
        $token->checkAndBecomesPollen($player);

        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        $player->giveExtraTime();
        Stats::inc("actions_s".ACTION_TYPE_TWOBEATS,$pId);
        Stats::inc("actions",$pId);
        Stats::inc("tokens_board",$pId,+1);

        $this->gamestate->nextPrivateState($pId, 'next');
    }
 
    /**
     * @param int $playerId
     * @return array List of possible spaces. Example [[ 'row' => 1, 'col' => 5 ],]
     */
    public function listSpotsForTwoBeats($playerId){
        $spots = [];
        $boardTokens = Tokens::getAllOnPersonalBoard($playerId);
        for($row = ROW_MIN; $row <=ROW_MAX; $row++ ){
            for($column = COLUMN_MIN; $column <=COLUMN_MAX; $column++ ){
                if($this->canPlayTwoBeats($row,$column,$boardTokens)){
                    $spots[] = [ 'row' => $row, 'col' => $column ];
                }
            }
        }
        return $spots;
    }
    
    /**
     * @param int $row COORD of new white token
     * @param int $column COORD of new white token
     * @param Collection $boardTokens 
     * @return bool + TRUE if this position can hold a new white token (ie. 8 tokens around)
     *  + FALSE otherwise
     */
    public function canPlayTwoBeats($row, $column,$boardTokens){
        if(StigmerianToken::isCoordOutOfGrid($row, $column)) return false; 
        $existing = Tokens::findTokenOnBoardWithCoord($boardTokens,$row, $column);
        if(isset($existing)) return false;//not empty spot

        $adjacentTokens = Tokens::listAdjacentTokensOnReadBoard($boardTokens,$row, $column,true);
        if($adjacentTokens->count() < MAX_GRID_NEIGHBOURS ) return false;

        return true;
    }

 
}
