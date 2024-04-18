<?php

namespace STIG\Models;

use STIG\Helpers\GridUtils;

/*
 * TokenCoord: all utility functions concerning a token coordinate on the grid
 */

class TokenCoord implements \JsonSerializable
{

  /**
   * Token type
   */
  public int $type;
  public int $row;
  public int $col;

  /**
   * @param int $type
   * @param int $row
   * @param int $column
   */
  public function __construct($type,$row,$column )
  {
    $this->type = $type;
    $this->col = $column;
    $this->row = $row;
  }
  
  /**
   */
  public function getUiData()
  {
    $data = $this->jsonSerialize();
    return $data;
  }
  /**
   * Return an array of attributes
   */
  public function jsonSerialize()
  {
    $data = [];
    $data['type'] = $this->type;
    $data['row'] = $this->row;
    $data['col'] = $this->col;
    $data['coord'] = GridUtils::getCoordName($this->row,$this->col);
    $data['pollen'] = $this->isPollen();
    $data['color'] = StigmerianToken::getTypeName($this->type);

    return $data;
  }
  
  /**
   * @param int $row
   * @param int $column
   * @return Collection
   */
  public static function listAdjacentCoords($row, $column)
  {
    $neighbours[] = new TokenCoord(0,$row -1, $column);
    $neighbours[] = new TokenCoord(0,$row +1, $column);
    $neighbours[] = new TokenCoord(0,$row, $column -1);
    $neighbours[] = new TokenCoord(0,$row, $column +1);
    return $neighbours;
  }
  
  /**
   * @return bool true if this token is on pollen side
   */
  public function isPollen()
  {
    if( array_search($this->type,TOKEN_POLLENS) === FALSE){
      return false;
    }
    return true;
  }
}
