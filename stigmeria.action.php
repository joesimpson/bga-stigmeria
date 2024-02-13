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
  	
  	// TODO: defines your action entry points there

    public function actCommonDrawAndLand()
    {
      self::setAjaxMode();
      $this->game->actCommonDrawAndLand();
      self::ajaxResponse();
    }
    public function actCommonMove()
    {
      self::setAjaxMode();
      $this->game->actCommonMove();
      self::ajaxResponse();
    }
    
    public function actGoToNext()
    {
      self::setAjaxMode();
      $this->game->actGoToNext();
      self::ajaxResponse();
    }
    public function actBackToCommon()
    {
      self::setAjaxMode();
      $this->game->actBackToCommon();
      self::ajaxResponse();
    }
    public function actDraw()
    {
      self::setAjaxMode();
      $this->game->actDraw();
      self::ajaxResponse();
    }
    public function actLand()
    {
      self::setAjaxMode();
      $this->game->actLand();
      self::ajaxResponse();
    }
    public function actMove()
    {
      self::setAjaxMode();
      $this->game->actMove();
      self::ajaxResponse();
    }
    public function actChoiceTokenToMove()
    {
      self::setAjaxMode();
      $token_id = self::getArg( "tokenId", AT_posint, true );
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actChoiceTokenToMove($token_id, $row, $col);
      self::ajaxResponse();
    }
    public function actChoiceTokenToLand()
    {
      self::setAjaxMode();
      $token_id = self::getArg( "tokenId", AT_posint, true );
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actChoiceTokenToLand($token_id, $row, $col);
      self::ajaxResponse();
    }
    public function actCentralLand()
    {
      self::setAjaxMode();
      $token_id = self::getArg( "tokenId", AT_posint, true );
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actCentralLand($token_id, $row, $col);
      self::ajaxResponse();
    }
    public function actCentralMove()
    {
      self::setAjaxMode();
      $token_id = self::getArg( "tokenId", AT_posint, true );
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actCentralMove($token_id, $row, $col);
      self::ajaxResponse();
    }
    public function actCancelChoiceTokenToLand()
    {
      self::setAjaxMode();
      $this->game->actCancelChoiceTokenToLand();
      self::ajaxResponse();
    }
    public function actCancelChoiceTokenToMove()
    {
      self::setAjaxMode();
      $this->game->actCancelChoiceTokenToMove();
      self::ajaxResponse();
    }
    public function actJoker()
    {
      self::setAjaxMode();
      $typeSource = self::getArg( "src", AT_posint, true );
      $typeDest = self::getArg( "dest", AT_posint, true );
      $this->game->actJoker($typeSource, $typeDest);
      self::ajaxResponse();
    }
    
    public function actSpecial()
    {
      self::setAjaxMode();
      $this->game->actSpecial();
      self::ajaxResponse();
    }
    
    public function actCancelSpecial()
    {
      self::setAjaxMode();
      $this->game->actCancelSpecial();
      self::ajaxResponse();
    }
    
    public function actChoiceSpecial()
    {
      self::setAjaxMode();
      $actionType = self::getArg( "act", AT_posint, true );
      $this->game->actChoiceSpecial($actionType);
      self::ajaxResponse();
    }
    
    public function actMerge()
    {
      self::setAjaxMode();
      $token1 = self::getArg( "t1", AT_posint, true );
      $token2 = self::getArg( "t2", AT_posint, true );
      $this->game->actMerge($token1,$token2);
      self::ajaxResponse();
    }
    public function actDiagonal()
    {
      self::setAjaxMode();
      $token_id = self::getArg( "tokenId", AT_posint, true );
      $row = self::getArg( "row", AT_posint, true );
      $col = self::getArg( "col", AT_posint, true );
      $this->game->actDiagonal($token_id, $row, $col);
      self::ajaxResponse();
    }

    public function actLetNextPlay()
    {
      self::setAjaxMode();
      $this->game->actLetNextPlay();
      self::ajaxResponse();
    }
    
    public function actEndTurn()
    {
      self::setAjaxMode();
      $this->game->actEndTurn();
      self::ajaxResponse();
    }
  
  }
  

