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
        $actions[] = ['type' =>  ACTION_TYPE_MIXING];
        break;
      case OPTION_FLOWER_MARONNE:
        $actions[] = ['type' =>  ACTION_TYPE_COMBINATION];
        $actions[] = ['type' =>  ACTION_TYPE_FULGURANCE];
        break;
        
      case OPTION_FLOWER_DENTDINE:
        $actions[] = ['type' =>  ACTION_TYPE_CHOREOGRAPHY];
        $actions[] = ['type' =>  ACTION_TYPE_DIAGONAL];
        $actions[] = ['type' =>  ACTION_TYPE_SWAP];
        $actions[] = ['type' =>  ACTION_TYPE_MOVE_FAST];
        break;
        
      case OPTION_FLOWER_SIFFLOCHAMP:
        $actions[] = ['type' =>  ACTION_TYPE_WHITE];
        $actions[] = ['type' =>  ACTION_TYPE_BLACK];
        $actions[] = ['type' =>  ACTION_TYPE_TWOBEATS];
        $actions[] = ['type' =>  ACTION_TYPE_REST];
        break;
      case OPTION_FLOWER_INSPIRACTRICE:
        //ALL THE PREVIOUS ONES
        $actions[] = ['type' =>  ACTION_TYPE_MIXING, ];
        $actions[] = ['type' =>  ACTION_TYPE_COMBINATION, ];
        $actions[] = ['type' =>  ACTION_TYPE_FULGURANCE, ];
        $actions[] = ['type' =>  ACTION_TYPE_CHOREOGRAPHY];
        $actions[] = ['type' =>  ACTION_TYPE_DIAGONAL, ];
        $actions[] = ['type' =>  ACTION_TYPE_SWAP, ];
        $actions[] = ['type' =>  ACTION_TYPE_MOVE_FAST];
        $actions[] = ['type' =>  ACTION_TYPE_WHITE, ];
        $actions[] = ['type' =>  ACTION_TYPE_BLACK, ];
        $actions[] = ['type' =>  ACTION_TYPE_TWOBEATS, ];
        $actions[] = ['type' =>  ACTION_TYPE_REST];
        break;
      default:
        break;
    }

    return $actions;
  }
}
