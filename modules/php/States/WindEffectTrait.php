<?php

namespace STIG\States;

trait WindEffectTrait
{
  
  public function stWindEffect()
  {
    $this->gamestate->nextState('next');
  }
}
