<?php

namespace STIG\States;

trait WindGenerationTrait
{
  
  public function stGenerateWind()
  {
    //TODO JSA SOUTH if not NO LIMIT
    
    $this->gamestate->nextState('next');
  }
}
