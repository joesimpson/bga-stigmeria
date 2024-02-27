<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Helpers\GridUtils;
use STIG\Managers\DiceRoll;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\DiceFace;
use STIG\Models\StigmerianToken;

/**
 * "Last Drift" alias "Ultime Dérive"
 */
trait LastDriftTrait
{ 
    /*
    Too late to autoskip ?
    public function stLastDrift($player_id)
    {
        $args = $this->argLastDrift($player_id);
        $autoSkip = $args['autoSkip']; 
        if($autoSkip){//when nothing needs to be done
            $player = Players::get($player_id);
            Notifications::lastDriftAutoSkip($player);
            $this->gamestate->nextPrivateState($player_id, 'next');
        }
    }
    */
    public function argLastDrift($player_id)
    {
        $lastDrift = PGlobals::getLastDrift($player_id);
        $actionType = $lastDrift['type'];
        $actionBoardPid = $lastDrift['pid'];
        $dieType = PGlobals::getLastDie($player_id);
        $dieFace = new DiceFace($dieType);
        $windDir = $dieFace->getWindDir();
        $actions = [];
        $args = [
            'die_face' => $dieType,
        ];
        $autoSkip = false;
        if(isset($windDir)){// N/S/E/W
            $actions[] = 'actLastDriftMove';
            if($actionType == ACTION_TYPE_LASTDRIFT_CENTRAL){
                $boardTokens = Tokens::getAllOnCentralBoard();
            }
            else {
                $boardTokens = Tokens::getAllOnPersonalBoard($actionBoardPid);
            }
            $args['p'] = $this->listPossibleMovesOnBoard($actionBoardPid,$boardTokens,$windDir);
            if(count($args['p']) == 0) {
                $autoSkip = true;
            }
            $args['dir'] = $windDir;
            $args['pid'] = $actionBoardPid;
        }
        else if($dieFace->isX()){// X
            $actions[] = 'actLastDriftRemove';
            $token_color = DiceRoll::getStigmerianFromDie($dieType);
            if($actionType == ACTION_TYPE_LASTDRIFT_CENTRAL){
                $boardTokens = Tokens::getAllOnCentralBoard([$token_color]);
            }
            else {
                $boardTokens = Tokens::getAllOnPersonalBoard($actionBoardPid, [$token_color]);
                $args['pid'] = $actionBoardPid;
            }
            $args['tokensIds'] = $boardTokens->getIds();
            $args['token_type'] = $token_color;
            $args['token_color'] = StigmerianToken::getTypeName($token_color);
            if(count($args['tokensIds']) == 0) {
                $autoSkip = true;
            }
        }
        else if($dieType == BLACK_NIGHT){//-
            if($actionType == ACTION_TYPE_LASTDRIFT_CENTRAL){
                $actions[] = 'actLastDriftLand';
                $args['p'] = $this->listPossiblePlacesOnCentralBoard();
                if(count($args['p']) == 0) {
                    $autoSkip = true;
                }
            }
            else {
                $args['pid'] = $actionBoardPid;
                //TODO JSA OTHERS BLACK_NIGHT special
                $autoSkip = true;
            }
        }
        $args['player_name2'] = isset($actionBoardPid) ? Players::get($actionBoardPid)->getName() : 'StigmaReine';
        $args['a'] = $actions;
        $args['autoSkip'] = $autoSkip;
        return $args;
    }
    
    /**
     * @param int $token_id id of token
     * @param int $row COORD of token
     * @param int $column COORD of token
     */
    public function actLastDriftMove($token_id, $row, $column)
    {
        self::checkAction( 'actLastDriftMove' ); 
        self::trace("actLastDriftMove($token_id, $row, $column)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($player->id, $player->getPrivateState());
 
        $lastDrift = PGlobals::getLastDrift($pId);
        $actionType = $lastDrift['type'];
        $actionBoardPid = $lastDrift['pid'];
        
        $dieType = PGlobals::getLastDie($pId);
        if(!isset($dieType)){
            throw new UnexpectedException(404,"Die roll not found");
        }
        $dieFace = new DiceFace($dieType);
        $windDir = $dieFace->getWindDir();
        $token = Tokens::get($token_id);
        $targetplayer = null;
        if(ACTION_TYPE_LASTDRIFT_CENTRAL!=$actionType){
            $targetplayer = Players::get($actionBoardPid);
            if($token->pId != $actionBoardPid || $token->location != TOKEN_LOCATION_PLAYER_BOARD ){
                throw new UnexpectedException(100,"You cannot move this token");
            } 
        }
        else if($token->location != TOKEN_LOCATION_CENTRAL_BOARD ){
            throw new UnexpectedException(100,"You cannot move this token");
        }
        $boardTokens = Tokens::getAllOnPersonalBoard($pId);
        if(!$this->canMoveOnPlayerBoard($pId,$token,$boardTokens,$row, $column)){
            throw new UnexpectedException(101,"You cannot move this token at $row, $column");
        }
        if(!GridUtils::isValidCellToMoveWithWind($windDir,$token->row,$token->col,$row,$column)){
            throw new UnexpectedException(101,"You cannot move this token at $row, $column");
        }

        //EFFECT
        if(isset($targetplayer)){
            $differentPlayer = $targetplayer->id != $pId;
            $fromCoord = $token->getCoordName();
            $token->moveToPlayerBoard($targetplayer,$row,$column,0,!$differentPlayer);
            if($differentPlayer) Notifications::moveOnPlayerBoard($player, $token,$fromCoord,$token->getCoordName(),0,$targetplayer);
        } else {
            $token->moveToCentralBoard($player,$row,$column,0);
        }

        $player->giveExtraTime();
        Stats::inc("actions_s".$actionType,$pId);
        Stats::inc("actions",$pId);
        if(isset($targetplayer)){
            if($differentPlayer && $targetplayer->isMultiactive()){
                //CHECKPOINT Opponent when targeted
                $this->addCheckpoint($targetplayer->getPrivateState(), $targetplayer->id );
            }
        }

        $this->addCheckpoint(ST_TURN_COMMON_BOARD,$pId);
        $this->gamestate->nextPrivateState($pId, 'next');
    }
    
    /**
     * @param int $token_id id of token
     */
    public function actLastDriftMoveOut($token_id)
    {
        self::checkAction( 'actLastDriftMoveOut' ); 
        self::trace("actLastDriftMoveOut($token_id)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($player->id, $player->getPrivateState());
 
        $lastDrift = PGlobals::getLastDrift($pId);
        $actionType = $lastDrift['type'];
        $actionBoardPid = $lastDrift['pid'];
        
        $dieType = PGlobals::getLastDie($pId);
        if(!isset($dieType)){
            throw new UnexpectedException(404,"Die roll not found");
        }
        $dieFace = new DiceFace($dieType);
        $windDir = $dieFace->getWindDir();
        $token = Tokens::get($token_id);
        $targetplayer = null;
        if(ACTION_TYPE_LASTDRIFT_CENTRAL!=$actionType){
            $targetplayer = Players::get($actionBoardPid);
            if($token->pId != $actionBoardPid || $token->location != TOKEN_LOCATION_PLAYER_BOARD ){
                throw new UnexpectedException(100,"You cannot move this token");
            } 
        }
        else if($token->location != TOKEN_LOCATION_CENTRAL_BOARD ){
            throw new UnexpectedException(100,"You cannot move this token");
        }
        if(!$this->canMoveOutOnBoard($token,$windDir)){
            throw new UnexpectedException(101,"You cannot move out this token");
        }

        //EFFECT
        $fromCoord = $token->getCoordName();
        if(isset($targetplayer)){
            $differentPlayer = $targetplayer->id != $pId;
            if($differentPlayer){
                //! RULE : move to current player recruitZone anyway !
                $token->moveToRecruitZone($player,0,false);
                Notifications::LDmoveOutRecruit($player, $token,$fromCoord,$targetplayer);
            }
            else {
                $token->moveToRecruitZone($player,0);
            }
        } else {
            $token->moveToRecruitZone($player,0,false);
            Notifications::LDmoveOutRecruit($player, $token,$fromCoord);
        }

        $player->giveExtraTime();
        Stats::inc("actions_s".$actionType,$pId);
        Stats::inc("actions",$pId);
        if(isset($targetplayer)){
            if($differentPlayer && $targetplayer->isMultiactive()){
                //CHECKPOINT Opponent when targeted
                $this->addCheckpoint($targetplayer->getPrivateState(), $targetplayer->id );
            }
        }

        $this->addCheckpoint(ST_TURN_COMMON_BOARD,$pId);
        $this->gamestate->nextPrivateState($pId, 'next');
    }

     /**
     * @param int $token_id id of token
     */
    public function actLastDriftRemove($token_id)
    {
        self::checkAction( 'actLastDriftRemove' ); 
        self::trace("actLastDriftRemove($token_id)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($player->id, $player->getPrivateState());
 
        $lastDrift = PGlobals::getLastDrift($pId);
        $actionType = $lastDrift['type'];
        $actionBoardPid = $lastDrift['pid'];
        
        $dieType = PGlobals::getLastDie($pId);
        if(!isset($dieType)){
            throw new UnexpectedException(404,"Die roll not found");
        }
        $token = Tokens::get($token_id);
        $targetplayer = null;
        if(ACTION_TYPE_LASTDRIFT_CENTRAL!=$actionType){
            $targetplayer = Players::get($actionBoardPid);
            if($token->pId != $actionBoardPid || $token->location != TOKEN_LOCATION_PLAYER_BOARD ){
                throw new UnexpectedException(100,"You cannot remove this token");
            } 
        }
        else if($token->location != TOKEN_LOCATION_CENTRAL_BOARD ){
            throw new UnexpectedException(100,"You cannot remove this token");
        }
        $token_color = DiceRoll::getStigmerianFromDie($dieType);
        if($token_color != $token->getType() ){
            throw new UnexpectedException(100,"You must remove a $token_color token");
        }

        //EFFECT
        Notifications::lastDriftRemove($player,$token,$targetplayer); 
        Tokens::delete($token->id);

        $player->giveExtraTime();
        Stats::inc("actions_s".$actionType,$pId);
        Stats::inc("actions",$pId);
        if(isset($targetplayer)){
            Stats::inc("tokens_board",$targetplayer->id,-1);
            if($targetplayer->id != $pId && $targetplayer->isMultiactive()){
                //CHECKPOINT Opponent when targeted
                $this->addCheckpoint($targetplayer->getPrivateState(), $targetplayer->id );
            }
        }

        $this->addCheckpoint(ST_TURN_COMMON_BOARD,$pId);
        $this->gamestate->nextPrivateState($pId, 'next');
    }

    
    /**
     * @param int $typeDest  
     * @param int $row COORD of token
     * @param int $column COORD of token
     */
    public function actLastDriftLand($typeDest, $row, $column)
    {
        self::checkAction( 'actLastDriftLand' ); 
        self::trace("actLastDriftLand($typeDest, $row, $column)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($player->id, $player->getPrivateState());
 
        $lastDrift = PGlobals::getLastDrift($pId);
        $actionType = $lastDrift['type'];
        
        if(!in_array($typeDest, STIG_PRIMARY_COLORS)){
            throw new UnexpectedException(11,"You cannot play a with dest color $typeDest");
        }
        $dieType = PGlobals::getLastDie($pId);
        if(!isset($dieType)){
            throw new UnexpectedException(404,"Die roll not found");
        }
        if(ACTION_TYPE_LASTDRIFT_CENTRAL!=$actionType){
            throw new UnexpectedException(100,"You cannot land a token");
        }
        if(BLACK_NIGHT != $dieType){
            throw new UnexpectedException(100,"You cannot land a token");
        }
        $boardTokens = Tokens::getAllOnCentralBoard();
        if(!$this->canPlaceOnCentralBoard($boardTokens,$row, $column)){
            throw new UnexpectedException(30,"You cannot place this token at $row, $column");
        }

        //EFFECT CREATE TOKEN
        $token = Tokens::createToken([
            'type' => $typeDest,
            'location' => TOKEN_LOCATION_CENTRAL_BOARD,
            'y'=> $row,
            'x'=> $column,
        ]);
        Notifications::moveToCentralBoard($player,$token,0);

        $player->giveExtraTime();
        Stats::inc("actions_s".$actionType,$pId);
        Stats::inc("actions",$pId);

        $this->addCheckpoint(ST_TURN_COMMON_BOARD,$pId);
        $this->gamestate->nextPrivateState($pId, 'next');
    }
}
