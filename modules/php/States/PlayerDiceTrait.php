<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\DiceRoll;
use STIG\Managers\Players;
use STIG\Models\DiceFace;

trait PlayerDiceTrait
{
    public function argWPlayerDice()
    { 
        $lastDie = Globals::getLastDie();
        $weatherTurn = '';
        $dieType = '';
        $actions = [];
        if(isset($lastDie)){
            
            if(array_key_exists('die',$lastDie) ){
                $dieType = $lastDie['die'];
                $previousDiceFace = new DiceFace($dieType);
                if($previousDiceFace->askPlayerReroll()){
                    $actions[] = 'actReroll';
                }
                if($previousDiceFace->askPlayerNoChoice()){
                    $actions[] = 'actDiceFaceU';
                }
                if($previousDiceFace->askPlayerChoice()){
                    $actions[] = 'actDiceFace';
                }
            }
            if(array_key_exists('turn',$lastDie) ){
                $weatherTurn = $lastDie['turn'];
            }
        }
        $args  = [
            'a' => $actions,
            'n' => $weatherTurn,
            'die_face' => $dieType,
        ];
        return $args;
    }
    
    public function actReroll()
    {
        self::checkAction( 'actReroll' ); 
        self::trace("actReroll()");

        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep( $pId, ST_WEATHER_PLAYER_DICE);

        //check not black night
        $lastDie = Globals::getLastDie();
        $previousDiceFace = new DiceFace($lastDie['die']);
        if(!$previousDiceFace->askPlayerReroll()){
            throw new UnexpectedException(405,"Not supported re roll for die face");
        }

        //EFFECT
        $diceFace = DiceRoll::rollNew();
        $weatherTurn = $lastDie['turn'];
        $lastDie['die'] = $diceFace->type;
        Globals::setLastDie($lastDie);
        $player->giveExtraTime();

        $this->addCheckpoint(ST_WEATHER_PLAYER_DICE);
        if( $diceFace->askPlayerNoChoice() ||$diceFace->askPlayerChoice() || $diceFace->askPlayerReroll()){
            Notifications::weatherDice($diceFace,$weatherTurn,$player,null);
            $this->gamestate->nextState('continue');
            return;
        }
        else {
            $newWind = $diceFace->getWindDir();
            Notifications::weatherDice($diceFace,$weatherTurn,$player,$newWind);
            Globals::setWindDir($weatherTurn, $newWind);
            $this->gamestate->nextState("next");
            return;
        }
    }
    
    public function actDiceFace($type)
    {
        self::checkAction( 'actDiceFace' ); 
        self::trace("actDiceFace($type)");

        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep( $pId, ST_WEATHER_PLAYER_DICE);

        if(!in_array($type,WIND_DIRECTIONS)){
            throw new UnexpectedException(404,"Not supported wind direction $type");
        }
        //check dice is black night OR X might choose only UNKNOWN
        $lastDie = Globals::getLastDie();
        $previousDiceFace = new DiceFace($lastDie['die']);
        if(!$previousDiceFace->askPlayerChoice() && WIND_DIR_UNKNOWN !=$type ){
            throw new UnexpectedException(405,"Not supported wind choice $type");
        }
        if(!$previousDiceFace->askPlayerNoChoice() && WIND_DIR_UNKNOWN ==$type ){
            throw new UnexpectedException(405,"Not supported wind choice $type");
        }
        $stateFrom = $lastDie['stateFrom'];

        //EFFECT
        $weatherTurn = $lastDie['turn'];
        $newWind = $type;
        Globals::setWindDir($weatherTurn, $newWind);
        
        Notifications::weatherDiceChoice($weatherTurn,$player,$newWind);
        $player->giveExtraTime();
        
        if($stateFrom == ST_WIND_EFFECT){
            $this->gamestate->nextState("nextEffect");
            return;
        }

        $this->gamestate->nextState("next");

    }
}
