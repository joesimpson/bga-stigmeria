<?php

namespace STIG\Models;

use STIG\Helpers\Collection;
use STIG\Models\TokenCoord;

/*
 * Schema: all utility functions concerning a schema 'goal' card 
 * -> not saved in DB because we have only 1 in each game, defined by a global option
 */

class Schema implements \JsonSerializable
{
  public int $id;
  /**
   * + vertigHaineuse (BEGINNER),
   * + Maronne,  
   * + SiffloChamp, 
   * + DentDine, 
   * + InspirActrice (EXPERT)
   * + Competitive
   * + Competitive no Limit
  */
  public string $type;
  /**
   * 1->4
   */
  public int $difficulty;
  /**
   * starting layout = array of TokenCoord
   */
  public Collection $start;
  /**
   * ending layout = array of TokenCoord
   */
  public Collection $end;

  /**
   * @param int $id
   * @param string $type
   * @param int $difficulty
   * @param array $start list of TokenCoord
   * @param array $end list of TokenCoord
   */
  public function __construct($id,$type,$difficulty,$start,$end)
  {
    $this->id = $id;
    $this->type = $type;
    $this->difficulty = $difficulty;
    $this->start = new Collection($start);
    $this->end = new Collection($end);
  }

  /**
   */
  public function getUiData()
  {
    $data = $this->jsonSerialize();
    $data['start'] = $this->start->ui();
    $data['end'] = $this->end->ui();
    return $data;
  }

  /**
   * Return an array of attributes
   */
  public function jsonSerialize()
  {
    $data = [];
    $data['id'] = $this->id;
    $data['type'] = $this->type;
    $data['difficulty'] = $this->difficulty;

    return $data;
  }

  /**
   * @return array the list of special action types when playing in normal mode
   */
  public function getNormalPlayerActions()
  {
    $actions = [];
    $flowerType = $this->type;
    switch($flowerType){
      case OPTION_FLOWER_VERTIGHAINEUSE:
        $actions[] = ['type' =>  ACTION_TYPE_MIXING, 'state'=> ACTION_STATE_UNLOCKED_FOREVER];
        break;
      case OPTION_FLOWER_MARONNE:
        $actions[] = ['type' =>  ACTION_TYPE_COMBINATION, 'state'=> ACTION_STATE_UNLOCKED_FOREVER];
        $actions[] = ['type' =>  ACTION_TYPE_FULGURANCE, 'state'=> ACTION_STATE_UNLOCKED_FOREVER];
        break;
        
      case OPTION_FLOWER_DENTDINE:
        $actions[] = ['type' =>  ACTION_TYPE_CHOREOGRAPHY, 'state'=> ACTION_STATE_UNLOCKED_FOR_ONCE_PER_TURN];
        $actions[] = ['type' =>  ACTION_TYPE_DIAGONAL, 'state'=> ACTION_STATE_UNLOCKED_FOREVER];
        $actions[] = ['type' =>  ACTION_TYPE_SWAP, 'state'=> ACTION_STATE_UNLOCKED_FOREVER];
        $actions[] = ['type' =>  ACTION_TYPE_MOVE_FAST, 'state'=> ACTION_STATE_UNLOCKED_FOR_ONCE_PER_TURN];
        break;
        
      case OPTION_FLOWER_SIFFLOCHAMP:
        $actions[] = ['type' =>  ACTION_TYPE_WHITE, 'state'=> ACTION_STATE_UNLOCKED_FOREVER];
        $actions[] = ['type' =>  ACTION_TYPE_BLACK, 'state'=> ACTION_STATE_UNLOCKED_FOREVER];
        $actions[] = ['type' =>  ACTION_TYPE_TWOBEATS, 'state'=> ACTION_STATE_UNLOCKED_FOREVER];
        $actions[] = ['type' =>  ACTION_TYPE_REST, 'state'=> ACTION_STATE_UNLOCKED_FOR_ONCE_PER_TURN];
        break;
      case OPTION_FLOWER_INSPIRACTRICE:
        //ALL THE PREVIOUS ONES
        $actions[] = ['type' =>  ACTION_TYPE_MIXING, 'state'=> ACTION_STATE_UNLOCKED_FOREVER];
        $actions[] = ['type' =>  ACTION_TYPE_COMBINATION, 'state'=> ACTION_STATE_UNLOCKED_FOREVER];
        $actions[] = ['type' =>  ACTION_TYPE_FULGURANCE, 'state'=> ACTION_STATE_UNLOCKED_FOREVER];
        $actions[] = ['type' =>  ACTION_TYPE_CHOREOGRAPHY, 'state'=> ACTION_STATE_UNLOCKED_FOR_ONCE_PER_TURN];
        $actions[] = ['type' =>  ACTION_TYPE_DIAGONAL, 'state'=> ACTION_STATE_UNLOCKED_FOREVER];
        $actions[] = ['type' =>  ACTION_TYPE_SWAP, 'state'=> ACTION_STATE_UNLOCKED_FOREVER];
        $actions[] = ['type' =>  ACTION_TYPE_MOVE_FAST, 'state'=> ACTION_STATE_UNLOCKED_FOR_ONCE_PER_TURN];
        $actions[] = ['type' =>  ACTION_TYPE_WHITE, 'state'=> ACTION_STATE_UNLOCKED_FOREVER];
        $actions[] = ['type' =>  ACTION_TYPE_BLACK, 'state'=> ACTION_STATE_UNLOCKED_FOREVER];
        $actions[] = ['type' =>  ACTION_TYPE_TWOBEATS, 'state'=> ACTION_STATE_UNLOCKED_FOREVER];
        $actions[] = ['type' =>  ACTION_TYPE_REST, 'state'=> ACTION_STATE_UNLOCKED_FOR_ONCE_PER_TURN];
        break;
      default:
        break;
    }

    return $actions;
  }
}
