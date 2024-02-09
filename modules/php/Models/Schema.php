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
   * vertigHaineuse (BEGINNER),Maronne,  SiffloChamp, DentDine, InspirActrice (EXPERT)
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
}
