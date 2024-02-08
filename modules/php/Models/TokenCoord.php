<?php

namespace STIG\Models;

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

    return $data;
  }
  
}
