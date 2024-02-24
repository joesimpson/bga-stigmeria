<?php

namespace STIG\Models;

/*
 * DiceFace: all utility functions concerning a die/dice roll (12 faces in this game)
 */

class DiceFace implements \JsonSerializable
{
  /**
   * Face type : 12 unique faces
   */
  public int $type;

  public function __construct($type)
  {
    
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
    return $data;
  }
 
}
