<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\DiceRoll;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait SpecialFogDieTrait
{
    public function stSpFogDie($pId)
    { 
      self::trace("stSpFogDie($pId)");
  
      $this->addCheckpoint(ST_TURN_SPECIAL_ACT_FOGDIE,$pId);
    }

    public function argSpFogDie($player_id)
    {
        $dieType = PGlobals::getLastDie($player_id);
        $token_color = DiceRoll::getStigmerianFromDie($dieType);
        $spots = $this->listPossiblePlacesOnPersonalBoard($player_id);
        return [
            'p' => $spots,
            'die_face' => $dieType,
            'token_type' => $token_color,
            'token_color' => StigmerianToken::getTypeName($token_color),
        ];
    }
    
    /**
     * "Fog Die" Action alias "Dé Brouillard"
     * @param int $row COORD of token
     * @param int $column COORD of token
     */
    public function actFogDie($row, $column)
    {
        self::checkAction( 'actFogDie' ); 
        self::trace("actFogDie($row, $column)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($player->id, $player->getPrivateState());
 
        $actionType = ACTION_TYPE_FOGDIE;
        $playerAction = PlayerActions::getPlayer($pId,[$actionType])->first();
        if(!isset($playerAction)){
            throw new UnexpectedException(404,"Not found player action $actionType for $pId");
        }
        if(!$playerAction->canBePlayed($player->countRemainingPersonalActions())){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $actionCost = $playerAction->getCost();
        $boardTokens = Tokens::getAllOnPersonalBoard($pId);
        if(!$this->canPlaceOnPlayerBoard($boardTokens,$row, $column)){
            throw new UnexpectedException(153,"You cannot place there");
        }
        $dieType = PGlobals::getLastDie($pId);
        if(!isset($dieType)){
            throw new UnexpectedException(404,"Die roll not found");
        }

        //EFFECT
        $token = Tokens::createToken([
            'type' => DiceRoll::getStigmerianFromDie($dieType),
            'location' => TOKEN_LOCATION_PLAYER_BOARD,
            'player_id' => $pId,
            'y' => $row,
            'x' => $column,
        ]);
        Notifications::spFogDie($player,$token,$actionCost);
        $token->checkAndBecomesPollen($player);

        $player->incNbPersonalActionsDone($actionCost);
        $playerAction->setNewStateAfterUse();
        Notifications::useActions($player,$playerAction);
        $player->giveExtraTime();
        Stats::inc("actions_s".$actionType,$pId);
        Stats::inc("actions",$pId);
        Stats::inc("tokens_board",$pId,+1);

        PGlobals::setState($player->id, ST_TURN_PERSONAL_BOARD);
        $this->gamestate->nextPrivateState($pId, 'next');
    }
}
