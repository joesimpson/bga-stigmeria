<?php
/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * Stigmeria implementation : © joesimpson <1324811+joesimpson@users.noreply.github.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * stigmeria.action.php
 *
 * Stigmeria main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/stigmeria/stigmeria/myAction.html", ...)
 *
 */
  
  
  class action_stigmeria extends APP_GameAction
  { 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "stigmeria_stigmeria";
            self::trace( "Complete reinitialization of board game" );
      }
  	} 
  	
    /* Check Helper, not a real action */
    private function checkVersion()
    {
        $clientVersion = (int) self::getArg('version', AT_int, false);
        $this->game->checkVersion($clientVersion);
    }
  	// TODO: defines your action entry points there

    public function actReroll()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actReroll();
      self::ajaxResponse();
    }
    public function actDiceFace()
    {
      self::setAjaxMode();
      self::checkVersion();
      $type = self::getArg( "t", AT_enum, true,null,WIND_DIRECTIONS  );
      $this->game->actDiceFace($type);
      self::ajaxResponse();
    }
    public function actFT()
    {
      self::setAjaxMode();
      self::checkVersion();
      $typeSource = self::getArg( "t", AT_posint, true );
      $this->game->actFT($typeSource);
      self::ajaxResponse();
    }
    public function actCommonDrawAndLand()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actCommonDrawAndLand();
      self::ajaxResponse();
    }
    public function actCommonMove()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actCommonMove();
      self::ajaxResponse();
    }
    
    public function actLastDrift()
    {
      self::setAjaxMode();
      self::checkVersion();
      $type = self::getArg( "act", AT_posint, true );
      $this->game->actLastDrift($type);
      self::ajaxResponse();
    }
    public function actLastDriftMove()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token_id = self::getArg( "tokenId", AT_posint, true );
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actLastDriftMove($token_id, $row, $col);
      self::ajaxResponse();
    }
    public function actLastDriftMoveOut()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token_id = self::getArg( "tokenId", AT_posint, true );
      $this->game->actLastDriftMoveOut($token_id);
      self::ajaxResponse();
    }
    public function actLastDriftRemove()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token_id = self::getArg( "tokenId", AT_posint, true );
      $this->game->actLastDriftRemove($token_id);
      self::ajaxResponse();
    }
    public function actLastDriftLand()
    {
      self::setAjaxMode();
      self::checkVersion();
      $type = self::getArg( "dest", AT_posint, true );
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actLastDriftLand($type, $row, $col);
      self::ajaxResponse();
    }
    public function actGoToNext()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actGoToNext();
      self::ajaxResponse();
    }
    public function actBackToCommon()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actBackToCommon();
      self::ajaxResponse();
    }
    public function actDraw()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actDraw();
      self::ajaxResponse();
    }
    public function actLand()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actLand();
      self::ajaxResponse();
    }
    public function actMove()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actMove();
      self::ajaxResponse();
    }
    public function actChoiceTokenToMove()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token_id = self::getArg( "tokenId", AT_posint, true );
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actChoiceTokenToMove($token_id, $row, $col);
      self::ajaxResponse();
    }
    public function actMoveOut()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token_id = self::getArg( "tokenId", AT_posint, true );
      $this->game->actMoveOut($token_id);
      self::ajaxResponse();
    }
    public function actSRecruit()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actSRecruit();
      self::ajaxResponse();
    }
    public function actSRecruitToken()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token_id = self::getArg( "t", AT_posint, true );
      $this->game->actSRecruitToken($token_id);
      self::ajaxResponse();
    }
    public function actCentralMoveOut()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token_id = self::getArg( "tokenId", AT_posint, true );
      $this->game->actCentralMoveOut($token_id);
      self::ajaxResponse();
    }
    public function actChoiceTokenToLand()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token_id = self::getArg( "tokenId", AT_posint, true );
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actChoiceTokenToLand($token_id, $row, $col);
      self::ajaxResponse();
    }
    public function actCentralLand()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token_id = self::getArg( "tokenId", AT_posint, true );
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actCentralLand($token_id, $row, $col);
      self::ajaxResponse();
    }
    public function actCentralMove()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token_id = self::getArg( "tokenId", AT_posint, true );
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actCentralMove($token_id, $row, $col);
      self::ajaxResponse();
    }
    public function actCancel()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actCancel();
      self::ajaxResponse();
    }
    public function actCancelChoiceTokenToLand()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actCancelChoiceTokenToLand();
      self::ajaxResponse();
    }
    public function actCancelChoiceTokenToMove()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actCancelChoiceTokenToMove();
      self::ajaxResponse();
    }
    public function actChooseSp()
    {
      self::setAjaxMode();
      self::checkVersion();
      $actionType = self::getArg( "act", AT_posint, true );
      $this->game->actChooseSp($actionType);
      self::ajaxResponse();
    }
    public function actGiveTokens()
    {
      self::setAjaxMode();
      self::checkVersion();
      $playerDestination = self::getArg( "pid", AT_posint, true );
      // ---------- ---------- array of token'ids  --------------------
      $token_ids_raw = self::getArg("tIds", AT_numberlist, true);
      // Removing last ';' if exists
      if (substr($token_ids_raw, -1) == ';')
        $token_ids_raw = substr($token_ids_raw, 0, -1);
      if ($token_ids_raw == '')
        $tokensArray = array();
      else
        $tokensArray = explode(';', $token_ids_raw);
      // ---------- ---------- -------------------- --------------------
      $this->game->actGiveTokens($tokensArray,$playerDestination);
      self::ajaxResponse();
    }
    public function actJoker()
    {
      self::setAjaxMode();
      self::checkVersion();
      $typeSource = self::getArg( "src", AT_posint, true );
      $typeDest = self::getArg( "dest", AT_posint, true );
      $this->game->actJoker($typeSource, $typeDest);
      self::ajaxResponse();
    }
    
    public function actCJokerS()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actCJokerS();
      self::ajaxResponse();
    }
    public function actCJoker()
    {
      self::setAjaxMode();
      self::checkVersion();
      $tokenId = self::getArg( "tokenId", AT_posint, true );
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actCJoker($tokenId,$row,$col);
      self::ajaxResponse();
    }
    
    public function actJealousy()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actJealousy();
      self::ajaxResponse();
    } 
    public function actSpJealousy()
    {
      self::setAjaxMode();
      self::checkVersion();
      $targetPlayerId = self::getArg( "p", AT_posint, true );
      $this->game->actSpJealousy($targetPlayerId);
      self::ajaxResponse();
    } 

    public function actSpecial()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actSpecial();
      self::ajaxResponse();
    }
    
    public function actCancelSpecial()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actCancelSpecial();
      self::ajaxResponse();
    }
    
    public function actChoiceSpecial()
    {
      self::setAjaxMode();
      self::checkVersion();
      $actionType = self::getArg( "act", AT_posint, true );
      $this->game->actChoiceSpecial($actionType);
      self::ajaxResponse();
    }
    
    public function actMixing()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token1 = self::getArg( "t1", AT_posint, true );
      $token2 = self::getArg( "t2", AT_posint, true );
      $this->game->actMixing($token1,$token2);
      self::ajaxResponse();
    }
    public function actCombination()
    {
      self::setAjaxMode();
      self::checkVersion();
      $tokenId = self::getArg( "tokenId", AT_posint, true );
      $this->game->actCombination($tokenId);
      self::ajaxResponse();
    } 
    public function actFulgurance()
    {
      self::setAjaxMode();
      self::checkVersion();
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actFulgurance($row, $col);
      self::ajaxResponse();
    }
    
    public function actChoreography()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token_id = self::getArg( "tokenId", AT_posint, true );
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actChoreography($token_id, $row, $col);
      self::ajaxResponse();
    }
    public function actChoreMoveOut()
    {
      self::setAjaxMode();
      self::checkVersion();
      $tokenId = self::getArg( "tokenId", AT_posint, true );
      $this->game->actChoreMoveOut($tokenId);
      self::ajaxResponse();
    }
    public function actChoreographyStop()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actChoreographyStop();
      self::ajaxResponse();
    }
    public function actDiagonal()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token_id = self::getArg( "tokenId", AT_posint, true );
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actDiagonal($token_id, $row, $col);
      self::ajaxResponse();
    }
    public function actSwap()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token1 = self::getArg( "t1", AT_posint, true );
      $token2 = self::getArg( "t2", AT_posint, true );
      $this->game->actSwap($token1,$token2);
      self::ajaxResponse();
    }

    public function actFastMove()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token_id = self::getArg( "tokenId", AT_posint, true );
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actFastMove($token_id, $row, $col);
      self::ajaxResponse();
    }
    public function actMoveOutFast()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token_id = self::getArg( "tokenId", AT_posint, true );
      $this->game->actMoveOutFast($token_id);
      self::ajaxResponse();
    }
    public function actWhite()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token1 = self::getArg( "t1", AT_posint, true );
      $token2 = self::getArg( "t2", AT_posint, true );
      $this->game->actWhite($token1,$token2);
      self::ajaxResponse();
    }
    public function actWhiteChoice()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token1 = self::getArg( "tokenId", AT_posint, true );
      $this->game->actWhiteChoice($token1);
      self::ajaxResponse();
    }
    
    public function actBlack1()
    {
      self::setAjaxMode();
      self::checkVersion();
      $token1 = self::getArg( "tokenId", AT_posint, true );
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actBlack1($token1,$row,$col);
      self::ajaxResponse();
    } 
    public function actTwoBeats()
    {
      self::setAjaxMode();
      self::checkVersion();
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actTwoBeats($row,$col);
      self::ajaxResponse();
    } 
    public function actRest()
    {
      self::setAjaxMode();
      self::checkVersion();
      $tokenId = self::getArg( "tokenId", AT_posint, true );
      $this->game->actRest($tokenId);
      self::ajaxResponse();
    } 
    public function actNSNK()
    {
      self::setAjaxMode();
      self::checkVersion();
      $typeSource = self::getArg( "src", AT_posint, true );
      $typeDest = self::getArg( "dest", AT_posint, true );
      $this->game->actNSNK($typeSource,$typeDest);
      self::ajaxResponse();
    } 
    public function actCopy()
    {
      self::setAjaxMode();
      self::checkVersion();
      $tokenId = self::getArg( "tokenId", AT_posint, true );
      $typeDest = self::getArg( "dest", AT_posint, true );
      $this->game->actCopy($tokenId,$typeDest);
      self::ajaxResponse();
    } 
    public function actPrediction()
    {
      self::setAjaxMode();
      self::checkVersion();
      // ---------- ---------- array of token'types  --------------------
      $token_types_raw = self::getArg("dest", AT_numberlist, true);
      // Removing last ';' if exists
      if (substr($token_types_raw, -1) == ';')
        $token_types_raw = substr($token_types_raw, 0, -1);
      if ($token_types_raw == '')
        $tokensArray = array();
      else
        $tokensArray = explode(';', $token_types_raw);
      // ---------- ---------- -------------------- --------------------
      $this->game->actPrediction($tokensArray);
      self::ajaxResponse();
    }
    public function actMimicry()
    {
      self::setAjaxMode();
      self::checkVersion();
      $typeDest = self::getArg( "dest", AT_posint, true );
      $this->game->actMimicry($typeDest);
      self::ajaxResponse();
    } 
    public function actFogDie()
    {
      self::setAjaxMode();
      self::checkVersion();
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actFogDie($row,$col);
      self::ajaxResponse();
    } 
    public function actPilferer()
    {
      self::setAjaxMode();
      self::checkVersion();
      $pid = self::getArg( "p", AT_posint, true );
      $this->game->actPilferer($pid);
      self::ajaxResponse();
    } 
    public function actSower()
    {
      self::setAjaxMode();
      self::checkVersion();
      $pid = self::getArg( "p", AT_posint, true );
      $typeDest = self::getArg( "t", AT_posint, true );
      $this->game->actSower($pid,$typeDest);
      self::ajaxResponse();
    } 
    public function actLetNextPlay()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actLetNextPlay();
      self::ajaxResponse();
    }
    
    public function actEndTurn()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actEndTurn();
      self::ajaxResponse();
    }
    

    public function actPass()
    {
      self::setAjaxMode();
      $this->game->actPass();
      self::ajaxResponse();
    }
    public function actCharmer1()
    {
      self::setAjaxMode();
      $this->game->actCharmer1();
      self::ajaxResponse();
    }
    public function actCharmer2()
    {
      self::setAjaxMode();
      self::checkVersion();
      $tokenId1 = self::getArg( "t1", AT_posint, true );
      $tokenId2 = self::getArg( "t2", AT_posint, true );
      $this->game->actCharmer2($tokenId1,$tokenId2);
      self::ajaxResponse();
    } 
    ///////////////////
    /////  UNDO   /////
    ///////////////////
    
    public function actConfirmTurn()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actConfirmTurn();
      self::ajaxResponse();
    }

    public function actRestart()
    {
      self::setAjaxMode();
      self::checkVersion();
      $this->game->actRestart();
      self::ajaxResponse();
    }

    public function actUndoToStep()
    {
      self::setAjaxMode();
      self::checkVersion();
      $stepId = self::getArg('stepId', AT_posint, false);
      $this->game->actUndoToStep($stepId);
      self::ajaxResponse();
    }

    ///////////////////
    /////  PREFS  /////
    ///////////////////

    public function actChangePref()
    {
      self::setAjaxMode();
      $pref = self::getArg('pref', AT_posint, false);
      $value = self::getArg('value', AT_posint, false);
      $this->game->actChangePreference($pref, $value);
      self::ajaxResponse();
    }
  }
  

