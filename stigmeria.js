/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * Stigmeria implementation : © joesimpson <1324811+joesimpson@users.noreply.github.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * stigmeria.js
 *
 * Stigmeria user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */
//Tisaac way to debug ;)
 var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
 var debug = isDebug ? console.info.bind(window.console) : function () {};

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    g_gamethemeurl + 'modules/js/Core/game.js',
    g_gamethemeurl + 'modules/js/Core/modal.js',
],
function (dojo, declare) {
    const TURN_MAX = 10;
    const ACTION_TYPE_MIXING = 10;
    const ACTION_TYPE_COMBINATION = 11;
    const ACTION_TYPE_FULGURANCE = 12;
    const ACTION_TYPE_CHOREOGRAPHY = 13;
    const ACTION_TYPE_DIAGONAL = 14;
    const ACTION_TYPE_SWAP = 15;
    const ACTION_TYPE_MOVE_FAST = 16;
    const ACTION_TYPE_WHITE = 20;
    const ACTION_TYPE_BLACK = 21;
    const ACTION_TYPE_TWOBEATS = 22;
    const ACTION_TYPE_REST = 23;

    const TOKEN_STIG_WHITE =    8;
    const TOKEN_STIG_BLACK =    9;
    const TOKEN_TYPE_NEWTURN = 21;
    
    const PREF_SCHEMA_BOARD_ORDER = 100;
    const PREF_STIGMAREINE_BOARD_ORDER = 101;
    const PREF_STIGMAREINE_BOARD_AUTO_ORDER = 103;
    const PREF_SP_BUTTONS = 102;

    return declare("bgagame.stigmeria", [customgame.game], {
        constructor: function(){
            console.log('stigmeria constructor');
              
            // Fix mobile viewport (remove CSS zoom)
            this.default_viewport = 'width=800';

            this._counters = {};

            this._notifications = [
                ['newRound', 10],
                ['newWinds', 10],
                ['clearTurn', 200],
                ['refreshUI', 200],
                ['newTurn', 800],
                ['endTurn', 500],
                ['updateFirstPlayer', 500],
                ['useActions', 500],
                ['drawToken', 900],
                ['drawTokenForCentral', 700],
                ['moveToCentralBoard', 900],
                ['moveOnCentralBoard', 900],
                ['moveToCentralRecruit', 900],
                ['putTokenInBag', 800],
                ['letNextPlay', 10],
                ['moveToPlayerBoard', 900],
                ['moveOnPlayerBoard', 900],
                ['moveFromDeckToPlayerBoard', 900],
                ['moveBackToRecruit', 900],
                ['moveBackToBox', 900],
                ['unlockSp',100],
                ['spMixing', 900],
                ['spCombination', 900],
                ['spSwap', 900],
                ['spWhite', 900],
                ['spBlack', 900],
                ['spTwoBeats', 900],
                ['spRest', 900],
                ['newPollen', 900],
                ['playJoker', 500],
                ['playCJoker', 500],
                ['windBlows', 1000],
                ['windElimination', 10],
                ['decklimination', 10],
                ['addPoints', 800],
            ];
            //For now I don't want to spoil my bar when other player plays, and multiactive state change is more complex
            this._displayNotifsOnTop = false;
            //TODO JSA disabled if restart is chaotic
            this._displayRestartButtons = true;
        },
        
        ///////////////////////////////////////////////////
        //     _____ ______ _______ _    _ _____  
        //    / ____|  ____|__   __| |  | |  __ \ 
        //   | (___ | |__     | |  | |  | | |__) |
        //    \___ \|  __|    | |  | |  | |  ___/ 
        //    ____) | |____   | |  | |__| | |     
        //   |_____/|______|  |_|   \____/|_|    
        /////////////////////////////////////////////////// 
        
        setup: function( gamedatas )
        {
            debug('SETUP', gamedatas);
            
            this.dontPreloadImage( 'flower1.jpg' );
            this.dontPreloadImage( 'flower2.jpg' );
            this.dontPreloadImage( 'flower3.jpg' );
            this.dontPreloadImage( 'flower4.jpg' );
            this.dontPreloadImage( 'flower5.jpg' );
            this.dontPreloadImage( 'actions.jpg' );

            this.setupCentralBoard();
            this.setupSchemaBoard();
            this.setupPlayers();
            this.setupInfoPanel();
            this.setupTokens();
            
            console.log( "Ending specific game setup" );

            this.inherited(arguments);
        },
        
        getSettingsConfig() {
            return {
                schemaBoardOrder: { type: 'pref', prefId: PREF_SCHEMA_BOARD_ORDER },
                centralBoardOrder: { type: 'pref', prefId: PREF_STIGMAREINE_BOARD_ORDER },
                centralBoardAutoOrder: { type: 'pref', prefId: PREF_STIGMAREINE_BOARD_AUTO_ORDER },
                boardWidth: {
                  default: 50,
                  name: _('Board width'),
                  type: 'slider',
                  sliderConfig: {
                    step: 2,
                    padding: 0,
                    range: {
                      min: [10],
                      max: [100],
                    },
                  },
                },
                takePieceWidth: {
                  default: 40,
                  name: _('Token width in selection'),
                  type: 'slider',
                  sliderConfig: {
                    step: 3,
                    padding: 0,
                    range: {
                      min: [10],
                      max: [100],
                    },
                  },
                },
                spButtonsStyle: { type: 'pref', prefId: PREF_SP_BUTTONS },
            };
        },
        
        onChangeBoardWidthSetting(val) {
            this.updateLayout();
        },
        onChangeTakePieceWidthSetting(val) {
            const ROOT = document.documentElement;
            const WIDTH = 200;
            let newWidth = (this.settings.takePieceWidth / 100) * WIDTH;
            /*
            dojo.query("#stig_select_piece_container .stig_token").forEach( i => {
                dojo.style(i.id, "width", `${newWidth}px`);
                dojo.style(i.id, "height", `${newWidth}px`);
                });
            */
            ROOT.style.setProperty('--stig_takePieceWidth', `${newWidth}px`);
        },
       

        ///////////////////////////////////////////////////
        //     _____ _______    _______ ______  _____ 
        //    / ____|__   __|/\|__   __|  ____|/ ____|
        //   | (___    | |  /  \  | |  | |__  | (___  
        //    \___ \   | | / /\ \ | |  |  __|  \___ \ 
        //    ____) |  | |/ ____ \| |  | |____ ____) |
        //   |_____/   |_/_/    \_\_|  |______|_____/ 
        ///////////////////////////////////////////////////
        
        onEnteringStateNextRound: function(args)
        {
            debug( 'onEnteringStateNextRound() ', args );
            
        }, 
        onEnteringStateGenerateWind: function(args)
        {
            debug( 'onEnteringStateGenerateWind() ', args );
            
        }, 
        onEnteringStatePlayerDice: function(args)
        {
            debug( 'onEnteringStatePlayerDice() ', args );
            
        }, 
        onEnteringStateNextTurn: function(args)
        {
            debug( 'onEnteringStateNextTurn() ', args );
            
        }, 
        onEnteringStatePlayerTurn: function(args)
        {
            debug( 'onEnteringStatePlayerTurn() ', args );
            this._counters['turn'].setValue(this.gamedatas.turn);
            this.forEachPlayer(  (player) => {
                //this._counters[player.id]['actions'].setValue(Math.min(this.gamedatas.turn,TURN_MAX));
            });
        }, 
        onEnteringStateCommonBoardTurn: function(args)
        {
            debug( 'onEnteringStateCommonBoardTurn() ', args );
            
            let possibleActions = args.a;
            let nbActions = args.n;
            if(nbActions>0){
                if(possibleActions.includes('actCommonDrawAndLand')){
                    this.addPrimaryActionButton('btnCommonDrawAndPlace', _('Draw and Place'), () => {
                        this.confirmationDialog(_("Are you sure to draw a token from your bag ?"), () => {
                            this.takeAction('actCommonDrawAndLand', {});
                        });
                    });
                }
                if(possibleActions.includes('actCommonMove')){
                    this.addPrimaryActionButton('btnCommonMove',  _('Move'), () => this.takeAction('actCommonMove', {}));
                }
            }
            if(possibleActions.includes('actCJoker')){
                this.addPrimaryActionButton(`btnCJoker`, _('Joker') , () =>  { this.takeAction('actCJokerS'); });
            }
            if(possibleActions.includes('actGoToNext')){
                this.addPrimaryActionButton('btnNext',  _('Next'), () => this.takeAction('actGoToNext', {}));
            }
            if($('stig_central_board_container_wrapper')) $('stig_central_board_container_wrapper').classList.add('stig_current_play');
        }, 
        onEnteringStateCentralChoiceTokenToLand: function(args)
        {
            debug( 'onEnteringStateCentralChoiceTokenToLand() ', args );
            $('stig_central_board_container_wrapper').classList.add('stig_current_play');
            let selectedToken = null;
            let selectedTokenType = null;
            Object.values(args.tokens).forEach((token) => {
                let elt = this.addToken(token, $('stig_select_piece_container'), '_tmp');
                if(args.tokens.length == 1) {
                    //AUTO SELECT
                    elt.classList.add('selected');
                    selectedTokenType = token.type;
                }
                else {
                    this.onClick(`${elt.id}`, () => {
                        if (selectedToken) $(`stig_token_${selectedToken}`).classList.remove('selected');
                        selectedToken = token.id + '_tmp';
                        $(`stig_token_${selectedToken}`).classList.add('selected');
                    });
                }
            });
            
            let selectedTokenCell = null;
            let centralBoard = $(`stig_central_board`);
            //Clean obsolete tokens:
            centralBoard.querySelectorAll('.stig_token_cell:not(.stig_token_holder)').forEach((oToken) => {
                    dojo.destroy(oToken);
                });
            //possible places to play :
            Object.values(args.p_places_p).forEach((coord) => {
                let row = coord.row;
                let column = coord.col;
                let elt = this.addSelectableTokenCell('central',row, column);
                if(selectedTokenType) elt.dataset.type = selectedTokenType;
                this.onClick(`stig_token_cell_central_${row}_${column}`, (evt) => {
                    let div = evt.target;
                    centralBoard.querySelectorAll('.stig_token_cell:not(.stig_token_holder)').forEach((oToken) => {
                        oToken.classList.remove('selected');
                    });
                    div.classList.toggle('selected');
                    $(`btnConfirm`).classList.remove('disabled');
                });
            });
            this.addPrimaryActionButton('btnConfirm', _('Confirm'), () => {
                let selectedToken = $(`stig_select_piece_container`).querySelector(`.stig_token.selected`);
                let selectedTokenCell = $(`stig_player_boards`).querySelector(`.stig_token_cell:not(.stig_token_holder).selected`);
                this.takeAction('actCentralLand', { tokenId: selectedToken.dataset.id,  row: selectedTokenCell.dataset.row, col:selectedTokenCell.dataset.col, });
            }); 
            //DISABLED by default
            $(`btnConfirm`).classList.add('disabled');
        }, 
        
        onEnteringStateCentralChoiceTokenToMove: function(args)
        {
            debug( 'onEnteringStateCentralChoiceTokenToMove() ', args );
            $('stig_central_board_container_wrapper').classList.add('stig_current_play');
            this.initTokenSelectionDest('actCentralMove', args.p_places_m,'central','actCentralMoveOut');
            this.addSecondaryActionButton('btnCancel',  _('Cancel'), () => this.takeAction('actCancelChoiceTokenToMove', {}));
        }, 
        
        onEnteringStateCJoker: function(args)
        {
            debug( 'onEnteringStateCJoker() ', args );

            $('stig_central_board_container_wrapper').classList.add('stig_current_play');
            let selectedToken = null;
            let board = $(`stig_central_board`);
            Object.values(args.tokens).forEach((token) => {
                let elt = this.addToken(token, $('stig_select_piece_container'), '_tmp');
                this.onClick(`${elt.id}`, () => {
                    //CLICK SELECT TOKEN
                    if (selectedToken) $(`stig_token_${selectedToken}`).classList.remove('selected');
                    selectedToken = token.id + '_tmp';
                    $(`stig_token_${selectedToken}`).classList.add('selected');
                            
                    //possible places to play :
                    Object.values(args.p_places_p).forEach((coord) => {
                        let row = coord.row;
                        let column = coord.col;
                        let elt2 = this.addSelectableTokenCell('central',row, column);
                        elt2.dataset.type = elt.dataset.type;
                        this.onClick(`${elt2.id}`, (evt) => {
                            //CLICK SELECT DESTINATION
                            board.querySelectorAll('.stig_token_cell:not(.stig_token_holder)').forEach((oToken) => {
                                oToken.classList.remove('selected');
                            });
                            let div = evt.target;
                            div.classList.toggle('selected');
                            $(`btnConfirm`).classList.remove('disabled');
                        });
                    });
                });
            });
            
            this.addPrimaryActionButton('btnConfirm', _('Confirm'), () => {
                let selectedToken = $(`stig_select_piece_container`).querySelector(`.stig_token.selected`);
                let selectedTokenCell = $(`stig_player_boards`).querySelector(`.stig_token_cell.selected`);
                this.takeAction('actCJoker', { tokenId: selectedToken.dataset.id,  row: selectedTokenCell.dataset.row, col:selectedTokenCell.dataset.col, });
            }); 
            //DISABLED by default
            $(`btnConfirm`).classList.add('disabled');
        },
        onEnteringStateGainSpecialAction: function(args)
        {
            debug( 'onEnteringStateGainSpecialAction() ', args );
            
            let possibleActions = args.a;
            let enabledActions = possibleActions;
            this.formatSpecialActionButton(_('Mixing'),ACTION_TYPE_MIXING,possibleActions,enabledActions,'actChooseSp');
            this.formatSpecialActionButton(_('Combination'),ACTION_TYPE_COMBINATION,possibleActions,enabledActions,'actChooseSp');
            this.formatSpecialActionButton(_('Fulgurance'),ACTION_TYPE_FULGURANCE,possibleActions,enabledActions,'actChooseSp');
            this.formatSpecialActionButton(_('Choreography'),ACTION_TYPE_CHOREOGRAPHY,possibleActions,enabledActions,'actChooseSp');
            this.formatSpecialActionButton(_('Diagonal'),ACTION_TYPE_DIAGONAL,possibleActions,enabledActions,'actChooseSp');
            this.formatSpecialActionButton(_('Exchange'),ACTION_TYPE_SWAP,possibleActions,enabledActions,'actChooseSp');
            this.formatSpecialActionButton(_('Fast Step'),ACTION_TYPE_MOVE_FAST,possibleActions,enabledActions,'actChooseSp');
            this.formatSpecialActionButton(_('Half Note'),ACTION_TYPE_WHITE,possibleActions,enabledActions,'actChooseSp');
            this.formatSpecialActionButton(_('Quarter Note'),ACTION_TYPE_BLACK,possibleActions,enabledActions,'actChooseSp');
            this.formatSpecialActionButton(_('Two Beats'),ACTION_TYPE_TWOBEATS,possibleActions,enabledActions,'actChooseSp');
            this.formatSpecialActionButton(_('Rest'),ACTION_TYPE_REST,possibleActions,enabledActions,'actChooseSp');

        }, 
            
        onEnteringStateGiveTokens: function(args)
        {
            debug( 'onEnteringStateGiveTokens() ', args );

            if($('stig_central_board_container_wrapper')) $('stig_central_board_container_wrapper').classList.add('stig_current_play');
            
            this.currentSelection = [];
            let possibleTokens = args.tokens;
            let playerDestination = null;
            this.forEachPlayer((player) => {
                let buttonText = (this.player_id == player.id) ? _('Put in my bag') : this.fsr(_('Put in ${player} bag'), { player: player.name });
                this.addPrimaryActionButton('btnConfirm'+player.id,  buttonText, () => { 
                    this.takeAction('actGiveTokens', {tIds: this.currentSelection.join(';'), pid: player.id, }); 
                } );
                $('btnConfirm'+player.id).classList.add('stig_button_giveTokens');
            });
            //DISABLED by default
            $(`customActions`).querySelectorAll(".stig_button_giveTokens").forEach((b) => {  b.classList.add('disabled'); });
            Object.values(possibleTokens).forEach((tokenId) => {
                //Click token 
                let divToken = $(`stig_token_${tokenId}`);
                if(!divToken) return;
                this.onClick(divToken.id, (evt) => {
                    let tokenIdInt = parseInt(tokenId);
                    let div = evt.target;
                    if(div.classList.contains('selected')){
                        //UNSELECT
                        div.classList.remove('selected');
                        this.currentSelection.splice(this.currentSelection.indexOf(tokenIdInt), 1); 
                    }
                    else {
                        //SELECT 
                        this.currentSelection.push(tokenIdInt);
                        div.classList.add('selected');
                    }
                              
                    $(`customActions`).querySelectorAll(".stig_button_giveTokens").forEach((b) => {  b.classList.add('disabled'); });
                    if (this.currentSelection.length > 0) {
                        this.addSecondaryActionButton('btnClear', _('Clear selection'), () => {
                            this.currentSelection = [];
                            [...$('stig_central_board').querySelectorAll('.stig_token.selected')].forEach((o) => o.classList.remove('selected'));
                            $(`customActions`).querySelectorAll(".stig_button_giveTokens").forEach((b) => {  b.classList.add('disabled'); });
                        });
                        $(`customActions`).querySelectorAll(".stig_button_giveTokens").forEach((b) => {  b.classList.remove('disabled'); });
                    }
                });
            });
        },
        
        onEnteringStatePersonalBoardTurn: function(args)
        {
            debug( 'onEnteringStatePersonalBoardTurn() ', args );
            
            let nbActions = args.n;
            let possibleActions = args.a;
            if(nbActions>0){
                this.addPrimaryActionButton('btnDraw', _('Recruit'), () => { 
                    this.confirmationDialog(_("Are you sure to draw a token from your bag ?"), () => {
                        this.takeAction('actDraw', {});
                    });
                });
                if(!possibleActions.includes('actDraw')){
                    $('btnDraw').classList.add("disabled");
                }
                this.addPrimaryActionButton('btnPlace', _('Land'), () => this.takeAction('actLand', {}));
                if(!possibleActions.includes('actLand')){
                    $('btnPlace').classList.add("disabled");
                }
                this.addPrimaryActionButton('btnMove', _('Move'), () => this.takeAction('actMove', {}));
                if(!possibleActions.includes('actMove')){
                    $('btnMove').classList.add("disabled");
                }
                this.gamedatas.players[this.player_id].npad = args.done;
                //updated via notif_useActions
                //this.updateTurnMarker(this.player_id,this.gamedatas.turn,args.done +1 );

                this.addImageActionButton('btnSpecialAction', `<div><div class='stig_icon_flower_violet'></div>`+_('Special')+`<div class='stig_icon_flower_violet stig_icon_flipped'></div></div>`, () => { this.takeAction('actSpecial', {}); });
                if(!possibleActions.includes('actSpecial')){
                    $('btnSpecialAction').classList.add("disabled");
                }
            }
            Object.values(args.pj).forEach((tokenColor) => {
                let src = tokenColor.src;
                let dest = tokenColor.dest;
                this.addImageActionButton(`btnJoker_${src}_${dest}`, `<div><div class='stig_qty'>4</div><div class='stig_button_token' data-type='${src}'></div> <i class="fa6 fa6-arrow-right"></i> <div class='stig_qty'>4</div> <div class='stig_button_token' data-type='${dest}'></div></div>`, () =>  {
                    this.confirmationDialog(_("This will update tokens in your recruitment zone. You won't be able to replay a Joker in the game !"), () => {
                        this.takeAction('actJoker', {src:src,dest:dest})
                    });
                });
            });
            if(possibleActions.includes('actLetNextPlay')){
                this.addSecondaryActionButton('btnLetNextPlay',  _('Start next player'), () => {
                    this.confirmationDialog(_("Next player will start their turn, so you will not be able to play VS actions for this turn."), () => {
                        this.takeAction('actLetNextPlay', {}) ;
                    });
                });
            }
            this.addDangerActionButton('btnEndTurn', _('End turn'), () => {
                if(nbActions>0){
                    this.confirmationDialog(_("Are you sure to end your turn ?"), () => {
                        this.takeAction('actEndTurn', {});
                    });
                }else{//auto confirm
                    this.takeAction('actEndTurn', {});
                }
            });
            //this.addSecondaryActionButton('btnReturn', 'Return', () => this.takeAction('actBackToCommon', {}));
        }, 
        
        onEnteringStateChoiceTokenToLand: function(args)
        {
            debug( 'onEnteringStateChoiceTokenToLand() ', args );
            
            this.addSecondaryActionButton('btnCancel',  _('Return'), () => this.takeAction('actCancelChoiceTokenToLand', {}));
            
            let playerBoard = $(`stig_player_board_${this.player_id}`);
            let selectedToken = null;
            Object.values(args.tokens).forEach((token) => {
                let elt = this.addToken(token, $('stig_select_piece_container'), '_tmp');
                this.onClick(`${elt.id}`, () => {
                    //CLICK SELECT TOKEN
                    if (selectedToken) $(`stig_token_${selectedToken}`).classList.remove('selected');
                    selectedToken = token.id + '_tmp';
                    $(`stig_token_${selectedToken}`).classList.add('selected');
                            
                    //possible places to play :
                    Object.values(args.p_places_p).forEach((coord) => {
                        let row = coord.row;
                        let column = coord.col;
                        let elt2 = this.addSelectableTokenCell(this.player_id,row, column);
                        elt2.dataset.type = elt.dataset.type;
                        this.onClick(`${elt2.id}`, (evt) => {
                            //CLICK SELECT DESTINATION
                            playerBoard.querySelectorAll('.stig_token_cell:not(.stig_token_holder)').forEach((oToken) => {
                                oToken.classList.remove('selected');
                            });
                            let div = evt.target;
                            div.classList.toggle('selected');
                            $(`btnConfirm`).classList.remove('disabled');
                        });
                    });
                });
            });
            
            this.addPrimaryActionButton('btnConfirm', _('Confirm'), () => {
                let selectedToken = $(`stig_select_piece_container`).querySelector(`.stig_token.selected`);
                let selectedTokenCell = $(`stig_player_boards`).querySelector(`.stig_token_cell.selected`);
                this.takeAction('actChoiceTokenToLand', { tokenId: selectedToken.dataset.id,  row: selectedTokenCell.dataset.row, col:selectedTokenCell.dataset.col, });
            }); 
            //DISABLED by default
            $(`btnConfirm`).classList.add('disabled');
        }, 
        
        onEnteringStateChoiceTokenToMove: function(args)
        {
            debug( 'onEnteringStateChoiceTokenToMove() ', args );
            
            this.initTokenSelectionDest('actChoiceTokenToMove', args.p_places_m, this.player_id,'actMoveOut');
            this.addSecondaryActionButton('btnCancel',  _('Return'), () => this.takeAction('actCancelChoiceTokenToMove', {}));
        }, 
        formatSpecialActionButton: function(text,actionType,possibleActions,enabledActions, actionName ='actChoiceSpecial') {
            debug("formatSpecialActionButton",text,actionType,possibleActions,enabledActions);
            if(possibleActions.includes(actionType)){
                let divText = `<div><div class='stig_sp_action_text'>`+_(text)+`</div><div class='stig_sp_action_image' data-type='${actionType}'></div></div>`;
                this.addImageActionButton('btnStartSp'+actionType,divText , () => this.takeAction(actionName, {act:actionType}));
                if(!enabledActions.includes(actionType)){
                    $('btnStartSp'+actionType).classList.add('disabled');
                }
            }
        },
        onEnteringStateSpecialAction: function(args)
        {
            debug( 'onEnteringStateSpecialAction() ', args );
            
            let possibleActions = args.a;
            let enabledActions = args.e;
            this.formatSpecialActionButton(_('Mixing'),ACTION_TYPE_MIXING,possibleActions,enabledActions);
            this.formatSpecialActionButton(_('Combination'),ACTION_TYPE_COMBINATION,possibleActions,enabledActions);
            this.formatSpecialActionButton(_('Fulgurance'),ACTION_TYPE_FULGURANCE,possibleActions,enabledActions);
            this.formatSpecialActionButton(_('Choreography'),ACTION_TYPE_CHOREOGRAPHY,possibleActions,enabledActions);
            this.formatSpecialActionButton(_('Diagonal'),ACTION_TYPE_DIAGONAL,possibleActions,enabledActions);
            this.formatSpecialActionButton(_('Exchange'),ACTION_TYPE_SWAP,possibleActions,enabledActions);
            this.formatSpecialActionButton(_('Fast Step'),ACTION_TYPE_MOVE_FAST,possibleActions,enabledActions);
            this.formatSpecialActionButton(_('Half Note'),ACTION_TYPE_WHITE,possibleActions,enabledActions);
            this.formatSpecialActionButton(_('Quarter Note'),ACTION_TYPE_BLACK,possibleActions,enabledActions);
            this.formatSpecialActionButton(_('Two Beats'),ACTION_TYPE_TWOBEATS,possibleActions,enabledActions);
            this.formatSpecialActionButton(_('Rest'),ACTION_TYPE_REST,possibleActions,enabledActions);

            this.addSecondaryActionButton('btnCancel', _('Return'), () => this.takeAction('actCancelSpecial', {}));
        }, 
        
        onEnteringStateSpMixing: function(args)
        {
            debug( 'onEnteringStateSpMixing() ', args );

            this.initMultiTokenSelection('actMixing',args.tokens);
            this.addSecondaryActionButton('btnCancel', _('Return'), () => this.takeAction('actCancelSpecial', {}));
        }, 
        
        onEnteringStateSpCombination: function(args)
        {
            debug( 'onEnteringStateSpCombination() ', args );
            this.addSecondaryActionButton('btnCancel', _('Return'), () => this.takeAction('actCancelSpecial', {}));
            this.initTokenSimpleSelection('actCombination', args.tokensIds);
        }, 
        onEnteringStateSpFulgurance: function(args)
        {
            debug( 'onEnteringStateSpFulgurance() ', args );
            this.addSecondaryActionButton('btnCancel', _('Return'), () => this.takeAction('actCancelSpecial', {}));
            let confirmMessage = _("Are you sure to draw 5 tokens from your bag and randomly place them from that place to the right ?")
            this.initCellSelection('actFulgurance', args.p_places_p, this.player_id,null,confirmMessage);
        }, 
        onEnteringStateSpDiagonal: function(args)
        {
            debug( 'onEnteringStateSpDiagonal() ', args );
            this.initTokenSelectionDest('actDiagonal', args.p_places_m, this.player_id);
            this.addSecondaryActionButton('btnCancel', _('Return'), () => this.takeAction('actCancelSpecial', {}));
        }, 
        onEnteringStateSpChoreography: function(args)
        {
            debug( 'onEnteringStateSpChoreography() ', args );
            this.initTokenSelectionDest('actChoreography', args.p_places_m, this.player_id,'actChoreMoveOut');
            if(args.n == args.max) this.addSecondaryActionButton('btnCancel', _('Return'), () => this.takeAction('actCancelSpecial', {}));
            this.addDangerActionButton('btnStop', _('Stop'), () => this.takeAction('actChoreographyStop', {}));
        }, 
        
        onEnteringStateSpSwap: function(args)
        {
            debug( 'onEnteringStateSpSwap() ', args );
            
            this.addSecondaryActionButton('btnCancel', _('Return'), () => this.takeAction('actCancelSpecial', {}));
            this.initMultiTokenSelection('actSwap',args.tokens);
        }, 
        
        onEnteringStateSpFastMove: function(args)
        {
            debug( 'onEnteringStateSpFastMove() ', args );

            this.initTokenSelectionDest('actFastMove', args.p_places_m, this.player_id,'actMoveOutFast');
            this.addSecondaryActionButton('btnCancel', _('Return'), () => this.takeAction('actCancelSpecial', {}));
        }, 
        
        onEnteringStateSpWhite: function(args)
        {
            debug( 'onEnteringStateSpWhite() ', args );

            this.addSecondaryActionButton('btnCancel', _('Return'), () => this.takeAction('actCancelSpecial', {}));
            this.addSecondaryActionButton('btnClearSelection', _('Clear selection'), () =>  {
                this.reinitTokensSelection(args.tokens);
            });
            this.initMultiTokenSelection('actWhite',args.tokens, (token1,token2) => {});
            
        }, 
        onEnteringStateSpWhiteChoice: function(args)
        {
            debug( 'onEnteringStateSpWhiteChoice() ', args );

            this.addSecondaryActionButton('btnCancel', _('Return'), () => this.takeAction('actCancelSpecial', {}));
            this.initTokenSimpleSelection('actWhiteChoice', args.tokensIds);
        }, 
        
        onEnteringStateSpBlack1: function(args)
        {
            debug( 'onEnteringStateSpBlack1() ', args );

            this.addSecondaryActionButton('btnCancel', _('Return'), () => this.takeAction('actCancelSpecial', {}));
            this.initTokenSelectionDest('actBlack1',args.tokens, this.player_id,null, TOKEN_STIG_BLACK);
            
        }, 
        
        onEnteringStateSpTwoBeats: function(args)
        {
            debug( 'onEnteringStateSpTwoBeats() ', args );

            this.addSecondaryActionButton('btnCancel', _('Return'), () => this.takeAction('actCancelSpecial', {}));
            this.initCellSelection('actTwoBeats', args.p_places_p, this.player_id,TOKEN_STIG_WHITE)
        }, 
        
        onEnteringStateSpRest: function(args)
        {
            debug( 'onEnteringStateSpRest() ', args );

            this.addSecondaryActionButton('btnCancel', _('Return'), () => this.takeAction('actCancelSpecial', {}));
            this.initTokenSimpleSelection('actRest', args.tokensIds);
        }, 
        onEnteringStateWindEffect: function(args)
        {
            debug( 'onEnteringStateWindEffect() ', args );
            
        },
        onEnteringStateEndRound: function(args)
        {
            debug( 'onEnteringStateEndRound() ', args );
            
        },
        onEnteringStateScoring: function(args)
        {
            debug( 'onEnteringStateScoring() ', args );
            
        },
        onEnteringStatePreEndOfGame: function(args)
        {
            debug( 'onEnteringStatePreEndOfGame() ', args );
            
        },

        onUpdateActivityPlayerTurn: function(args)
        {
            debug( 'onUpdateActivityPlayerTurn() ', args );
            if( !this.isCurrentPlayerActive() ){
                this.clearPossible();
            }
        }, 

        onLeavingState(stateName) {
            this.inherited(arguments);
            dojo.empty('stig_select_piece_container');
            this.removeEmptyCellHolders();
            if($('stig_central_board_container_wrapper')) $('stig_central_board_container_wrapper').classList.remove('stig_current_play');
        },

        
        onEnteringStateConfirmTurn(args) {
            this.addPrimaryActionButton('btnConfirmTurn', _('Confirm'), () => {
                this.takeAction('actConfirmTurn');
            });
        },
        undoToStep(stepId) {
            this.checkAction('actRestart');
            this.takeAction('actUndoToStep', { stepId }, false);
        },
        
        //////////////////////////////////////////////////////////////
        //    _   _       _   _  __ _           _   _                 
        //   | \ | |     | | (_)/ _(_)         | | (_)                
        //   |  \| | ___ | |_ _| |_ _  ___ __ _| |_ _  ___  _ __  ___ 
        //   | . ` |/ _ \| __| |  _| |/ __/ _` | __| |/ _ \| '_ \/ __|
        //   | |\  | (_) | |_| | | | | (_| (_| | |_| | (_) | | | \__ \
        //   |_| \_|\___/ \__|_|_| |_|\___\__,_|\__|_|\___/|_| |_|___/
        //                                                            
        //    
        //////////////////////////////////////////////////////////////
 
        notif_clearTurn(n) {
            debug('Notif: restarting turn', n);
            this.cancelLogs(n.args.notifIds);
        },
    
        notif_refreshUI(n) {
            debug('Notif: refreshing UI', n);
            if(this.player_id == n.args.player_id) this.clearPossible();
            [ 'players', 'tokens', 'actions'].forEach((value) => {
              this.gamedatas[value] = n.args.datas[value];
            });
            this.setupTokens();
            this.forEachPlayer((player) => {
                let pId = player.id;
                this.scoreCtrl[pId].toValue(player.score);
                this._counters[pId].tokens_recruit.toValue(player.tokens_recruit);
                this._counters[pId].tokens_deck.toValue(player.tokens_deck);
                this._counters[pId].pollens.toValue(player.pollens);
                this._counters[pId].jokers.toValue(player.jokerUsed ? 0:1);
                this._counters[pId].actions.toValue(player.npad);
                this._counters[pId].unlockedActions.toValue(player.ua);
                this._counters[pId].lockedActions.toValue(player.la);
                this.updateTurnMarker(pId,this.gamedatas.turn,player.npad+1);
            });
        },

        notif_newRound(n) {
            debug('notif_newRound: new round', n);
            this.gamedatas.schema = n.args.schema;
            this.gamedatas.tokens = n.args.tokens;
            this.gamedatas.turn = 0;
            this._counters['turn'].toValue(this.gamedatas.turn);
            this.gamedatas.players = n.args.players;
            this.forEachPlayer((player) => {
                this._counters[player.id]['tokens_recruit'].setValue(player.tokens_recruit);
                this._counters[player.id]['tokens_deck'].setValue(player.tokens_deck);
                this._counters[player.id]['pollens'].setValue(player.pollens);
                this._counters[player.id]['pollensMax'].setValue(this.getFlowerTotalPollens());
                this._counters[player.id]['jokers'].setValue(player.jokerUsed ? 0:1);
                this._counters[player.id]['actions'].setValue(player.npad);
                this._counters[player.id]['actionsMax'].setValue(this.gamedatas.turn);
                this._counters[player.id]['unlockedActions'].setValue(player.ua);
                this._counters[player.id]['lockedActions'].setValue(player.la);
            });
            
            this.setupTokens();
        },
        notif_newTurn(n) {
            debug('notif_newTurn: new turn', n);
            this._counters['turn'].toValue(n.args.n);
            this.forEachPlayer((player) => {
                this._counters[player.id]['actions'].setValue(0);
                this._counters[player.id]['actionsMax'].setValue(n.args.n);
                this.updateTurnMarker(player.id,n.args.n,1);
            }); 
        },
        notif_endTurn(n) {
            debug('notif_endTurn: end turn for one player', n);
            this.updateTurnMarker(n.args.player_id,this.gamedatas.turn,99);
        },
        notif_updateFirstPlayer(n) {
            debug('Notif: updating first player', n);
            this.gamedatas.firstPlayer = n.args.player_id;
            this.updateFirstPlayer();
        },
        notif_newWinds(n) {
            debug('notif_newWinds: new wind dirs', n);
            this.gamedatas.winds = n.args.winds;
            //TODO JSA display winds
        },
        notif_useActions(n) {
            debug('notif_useActions: player spent actions', n);
            this.gamedatas.winds = n.args.winds;
            this._counters[n.args.player_id]['actions'].toValue(n.args.npad);
            this._counters[n.args.player_id]['unlockedActions'].toValue(n.args.ua);
            this._counters[n.args.player_id]['lockedActions'].toValue(n.args.la);
            this.updateTurnMarker(n.args.player_id,this.gamedatas.turn,n.args.npad+1);
        },
        notif_drawToken(n) {
            debug('notif_drawToken: new token on player board', n);
            let token = n.args.token;
            let player_id = n.args.player_id;
            this.addToken(token, `stig_reserve_${player_id}_tokens_deck`);
            let div = $(`stig_token_${token.id}`);
            this._counters[player_id]['tokens_deck'].incValue(-1);
            this._counters[n.args.player_id]['tokens_recruit'].incValue(1);
            this.slide(div, this.getTokenContainer(token));
        },
        notif_drawTokenForCentral(n) {
            debug('notif_drawTokenForCentral: new token on central board', n);
            let token = n.args.token;
            let player_id = n.args.player_id;
            this._counters[player_id]['tokens_deck'].incValue(-1);
            if(player_id != this.player_id) return;//don't anim to others, but not private
            let div =  this.addToken(token, `stig_reserve_${player_id}_tokens_deck`);
            //destroy after slide because it will be displayed by onEnteringState
            this.slide(div, this.getTokenContainer(token), {destroy:true, changeParent: false});
        },
        notif_moveToCentralBoard(n) {
            debug('notif_moveToCentralBoard: new token on central board', n);
            let token = n.args.token;
            this.addToken(token, this.getVisibleTitleContainer());
            let div = $(`stig_token_${token.id}`);
            div.dataset.row = token.row;
            div.dataset.col = token.col;
            div.dataset.state = token.state;
            /*
            if(n.args.player_id!=this.player_id){
                //only for others, because the 'active' player would have updated the counter in their own state
                this._counters[n.args.player_id]['tokens_deck'].incValue(-1);
            }*/
            this.slide(div, this.getTokenContainer(token));
        },
        notif_moveOnCentralBoard(n) {
            debug('notif_moveOnCentralBoard: token moved on on central board', n);
            let token = n.args.token;
            let div = $(`stig_token_${token.id}`);
            let oldParent = div.parentElement;//token_holder
            div.dataset.row = token.row;
            div.dataset.col = token.col;
            div.dataset.state = token.state;
            this.slide(div, this.getTokenContainer(token)).then(() =>{
                dojo.destroy( $(`${oldParent.id}`));
                //TODO JSA destroy also previous selectable cell ?
            });
        },
        notif_moveToCentralRecruit(n) {
            debug('notif_moveToCentralRecruit: token moved out from central board', n);
            let token = n.args.token;
            let div = $(`stig_token_${token.id}`);
            if(!div) return;
            let oldParent = div.parentElement;//token_holder
            div.dataset.row = null;
            div.dataset.col = null;
            this.slide(div, this.getTokenContainer(token)).then(() =>{
                if(oldParent.classList.contains('stig_token_holder')) dojo.destroy( $(`${oldParent.id}`));
                this._counters[n.args.player_id]['tokens_recruit'].incValue(1);
            });
        },
        notif_putTokenInBag(n) {
            debug('notif_putTokenInBag: token moved to player bag', n);
            let token = n.args.token;
            let div = $(`stig_token_${token.id}`);
            if(!div) return;
            let oldParent = div.parentElement;//token_holder
            div.dataset.row = null;
            div.dataset.col = null;
            let destinationPlayer = n.args.player_id2;
            this.slide(div, `stig_reserve_${destinationPlayer}_tokens_deck`, {destroy: true,}).then(() =>{
                if(oldParent.classList.contains('stig_token_holder')) dojo.destroy( $(`${oldParent.id}`));
                this._counters[destinationPlayer]['tokens_deck'].incValue(1);
            });
        },
        notif_letNextPlay(n) {
            debug('notif_letNextPlay: ', n);
            if($(`btnLetNextPlay`) ) dojo.destroy($(`btnLetNextPlay`));
        },
        notif_moveFromDeckToPlayerBoard(n) {
            debug('notif_moveFromDeckToPlayerBoard: new token on player board from deck', n);
            let token = n.args.token;
            this.addToken(token, this.getVisibleTitleContainer());
            this._counters[n.args.player_id]['tokens_deck'].incValue(-1);
            let div = $(`stig_token_${token.id}`);
            div.dataset.row = token.row;
            div.dataset.col = token.col;
            this.slide(div, this.getTokenContainer(token));
        },
        notif_moveToPlayerBoard(n) {
            debug('notif_moveToPlayerBoard: new token on player board', n);
            let token = n.args.token;
            //Move from player RECRUIT ZONE to player board :
            this._counters[n.args.player_id]['tokens_recruit'].incValue(-1);
            let div = $(`stig_token_${token.id}`);
            div.dataset.row = token.row;
            div.dataset.col = token.col;
            //TODO JSA REMOVE TEST if useless
            div.dataset.state = token.state;
            this.slide(div, this.getTokenContainer(token));
        },
        notif_moveOnPlayerBoard(n) {
            debug('notif_moveOnPlayerBoard: token moved on player board', n);
            let token = n.args.token;
            let div = $(`stig_token_${token.id}`);
            let oldParent = div.parentElement;//token_holder
            div.dataset.row = token.row;
            div.dataset.col = token.col;
            div.dataset.state = token.state;
            this.slide(div, this.getTokenContainer(token)).then(() =>{
                dojo.destroy( $(`${oldParent.id}`));
                //TODO JSA destroy also previous selectable cell ?
            });
        },
        notif_moveBackToRecruit(n) {
            debug('notif_moveBackToRecruit: token moved out from board', n);
            let token = n.args.token;
            let div = $(`stig_token_${token.id}`);
            if(!div) return;
            let oldParent = div.parentElement;//token_holder
            div.dataset.row = null;
            div.dataset.col = null;
            this.slide(div, this.getTokenContainer(token)).then(() =>{
                if(oldParent.classList.contains('stig_token_holder')) dojo.destroy( $(`${oldParent.id}`));
                this._counters[n.args.player_id]['tokens_recruit'].incValue(1);
            });
        },
        notif_moveBackToBox(n) {
            debug('notif_moveBackToBox: token delete from board', n);
            let token = n.args.token;
            let div = $(`stig_token_${token.id}`);
            if(div){
                this.slide(div, this.getVisibleTitleContainer(), {
                    from: div.id,
                    destroy: true,    
                    phantom: false,
                    duration: 1200,
                }).then(() =>{});
            }
        },
        notif_newPollen(n) {
            debug('notif_newPollen: token is flipped !', n);
            let token = n.args.token;
            let div = $(`stig_token_${token.id}`);
            div.dataset.row = token.row;
            div.dataset.col = token.col;
            div.dataset.type = token.type;
            this.slide(div, this.getTokenContainer(token));
            this._counters[n.args.player_id]['pollens'].incValue(1);
        },
        notif_unlockSp(n) {
            debug('notif_unlockSp: new special action !', n);
            let spAction = n.args.action;
            this._counters[n.args.player_id]['unlockedActions'].incValue(1);
        },
        notif_spMixing(n) {
            debug('notif_spMixing: tokens are mixed !', n);
            let token1 = n.args.token1;
            let token2 = n.args.token2;
            let div1 = $(`stig_token_${token1.id}`);
            let div2 = $(`stig_token_${token2.id}`);
            div1.dataset.type = token1.type;
            div2.dataset.type = token2.type;
            this.animationBlink2Times(div1);
            this.animationBlink2Times(div2);
        },
        notif_spCombination(n) {
            debug('notif_spCombination: token becomes brown !', n);
            let token1 = n.args.token;
            let div1 = $(`stig_token_${token1.id}`);
            if(div1){
                div1.dataset.type = token1.type;
                this.animationBlink2Times(div1);
            }
        },
        notif_spSwap(n) {
            debug('notif_spSwap: tokens are swapped', n);
            let token1 = n.args.t1;
            let token2 = n.args.t2;
            let div1 = $(`stig_token_${token1.id}`);
            let div2 = $(`stig_token_${token2.id}`); 
            this.slide(div1, this.getTokenContainer(token1));
            this.slide(div2, this.getTokenContainer(token2));
        },
        notif_spWhite(n) {
            debug('notif_spWhite: tokens are merged !', n);
            let token1 = n.args.token1;
            let token2 = n.args.token2;
            let div1 = $(`stig_token_${token1.id}`);
            let div2 = $(`stig_token_${token2.id}`);
            div1.dataset.type = token1.type;
            if(div2){
                if(div2.parentElement.classList.contains('stig_token_holder')) dojo.destroy(div2.parentElement);
                else dojo.destroy(div2);
            }
            this.animationBlink2Times(div1);
        },
        notif_spBlack(n) {
            debug('notif_spBlack: tokens are black !', n);
            let token1 = n.args.token1;
            let token2 = n.args.token2;
            let div1 = $(`stig_token_${token1.id}`);
            div1.dataset.type = token1.type;
            this.animationBlink2Times(div1);
            let div2 = this.addToken(token2, this.getVisibleTitleContainer());
            this.slide(div2, this.getTokenContainer(token2));
        },
        
        notif_spTwoBeats(n) {
            debug('notif_spTwoBeats: new white !', n);
            let token = n.args.token;
            let div = this.addToken(token, this.getVisibleTitleContainer());
            this.slide(div, this.getTokenContainer(token));
        },
        notif_spRest(n) {
            debug('notif_spRest: token is removed !', n);
            let token = n.args.token;
            let isPollen = token.pollen == true;
            let div = $(`stig_token_${token.id}`);
            if(div){
                this.slide(div, this.getVisibleTitleContainer(), {
                    from: div.id,
                    destroy: true,    
                    phantom: false,
                    duration: 1200,
                }).then(() =>{
                    if(isPollen){
                        this._counters[n.args.player_id]['pollens'].incValue(-1);
                    }
                });
            }
        },
        notif_playJoker(n) {
            debug('notif_playJoker: tokens change color !', n);
            let tokens = n.args.tokens;
            Object.values(tokens).forEach((token) => {
                let div = $(`stig_token_${token.id}`);
                div.dataset.type = token.type;
                this.slide(div, this.getTokenContainer(token));
                //this.animationBlink2Times(div.id);
            });
            this._counters[n.args.player_id]['jokers'].incValue(-1);
        },
        notif_playCJoker(n) {
            debug('notif_playCJoker: token moved !', n);
            /*
            let token = n.args.token;
            let div = $(`stig_token_${token.id}`);
            if(div){
                this.slide(div, this.getTokenContainer(token));
            }
            */
            this._counters[n.args.player_id]['tokens_recruit'].incValue(-1);
            this._counters[n.args.player_id]['jokers'].incValue(-1);
        },
        notif_windBlows(n) {
            debug('notif_windBlows: tokens moved on board', n);
            let tokens = n.args.tokens;
            Object.values(tokens).forEach((token) => {
                let div = $(`stig_token_${token.id}`);
                div.dataset.row = token.row;
                div.dataset.col = token.col;
                div.dataset.state = token.state;
                this.slide(div, this.getTokenContainer(token));
            });
        },
        notif_windElimination(n) {
            debug('notif_windElimination', n);
            let player_id = n.args.player_id;
            let divPanel = `overall_player_board_${player_id}`;
            $(divPanel).classList.add('stig_eliminated');
        },
        notif_deckElimination(n) {
            debug('notif_deckElimination', n);
            let player_id = n.args.player_id;
            let divPanel = `overall_player_board_${player_id}`;
            $(divPanel).classList.add('stig_eliminated');
        },
       
        notif_addPoints(n) {
            debug('notif_addPoints: scoring !', n);
            let points = n.args.n;
            let player_id = n.args.player_id;
            this.scoreCtrl[ player_id ].incValue( points );
        },

        ///////////////////////////////////////////////////
        //    _    _ _   _ _     
        //   | |  | | | (_) |    
        //   | |  | | |_ _| |___ 
        //   | |  | | __| | / __|
        //   | |__| | |_| | \__ \
        //    \____/ \__|_|_|___/
        //                       
        ///////////////////////////////////////////////////
        onScreenWidthChange() {
            if (this.settings) this.updateLayout();
        },
    
        updateLayout() {
            if (!this.settings) return;
            const ROOT = document.documentElement;
    
            const WIDTH = $('stig_main_zone').getBoundingClientRect()['width'];
            const HEIGHT = (window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight) - 62;
            const BOARD_WIDTH = 1127;
            const BOARD_HEIGHT = 1176;
    
            let widthScale = ((this.settings.boardWidth / 100) * WIDTH) / BOARD_WIDTH,
            //heightScale = HEIGHT / BOARD_HEIGHT,
            //scale = Math.min(widthScale, heightScale);
            scale = widthScale;
            ROOT.style.setProperty('--stig_board_display_scale', scale);
    
        },

        getFlowerType(){
            if(this.gamedatas.schema && this.gamedatas.schemas){
                let schema = this.gamedatas.schemas[this.gamedatas.schema];
                return schema.type;
            }
            return 1;
        },
        getFlowerTotalPollens(){
            if(this.gamedatas.schema && this.gamedatas.schemas){
                let schema = this.gamedatas.schemas[this.gamedatas.schema];
                if(schema.end){
                    return schema.end.length;
                }
            }
            return 0;
        },
                
        ////////////////////////////////////////
        //  ____  _
        // |  _ \| | __ _ _   _  ___ _ __ ___
        // | |_) | |/ _` | | | |/ _ \ '__/ __|
        // |  __/| | (_| | |_| |  __/ |  \__ \
        // |_|   |_|\__,_|\__, |\___|_|  |___/
        //                |___/
        ////////////////////////////////////////

        setupPlayers() {
            let currentPlayerNo = 1;
            let nPlayers = 0;
            this.forEachPlayer((player) => {
                let isCurrent = player.id == this.player_id;
                let divPanel = `player_panel_content_${player.color}`;
                if(player.eliminated == true) $(`overall_player_board_${player.id}`).classList.add('stig_eliminated');
                this.place('tplPlayerPanel', player, divPanel, 'after');
                this.place('tplPlayerBoard', player, 'stig_player_boards');
                
                document.querySelectorAll('.stig_icon_container_tokens_recruit').forEach((e) => e.dataset.flower_type = this.getFlowerType());

                this.addTooltip(`stig_reserve_${player.id}_tokens_deck`, _('Tokens in bag'),'');
                this.addTooltip(`stig_reserve_${player.id}_tokens_recruit`, _('Tokens in recruit zone'),'');
                this.addTooltip(`stig_reserve_${player.id}_pollens`, _('Pollens on flower'),'');
                this.addTooltip(`stig_reserve_${player.id}_jokers`, _('Jokers'),'');
                this.addTooltip(`stig_reserve_${player.id}_actions`, _('Actions on player board'),'');
                this.addTooltip(`stig_reserve_${player.id}_unlockedActions`, _('Unlocked special actions'),'');
                this.addTooltip(`stig_reserve_${player.id}_lockedActions`, _('Locked special actions'),'');

                let pId = player.id;
                //let nbUnlockedActions = (this.gamedatas.actions[pId]!=undefined) ? this.gamedatas.actions[pId].length : 0;
                this._counters[pId] = {
                    tokens_recruit: this.createCounter(`stig_counter_${pId}_tokens_recruit`, player.tokens_recruit),
                    tokens_deck: this.createCounter(`stig_counter_${pId}_tokens_deck`, player.tokens_deck),
                    pollens: this.createCounter(`stig_counter_${pId}_pollens`, player.pollens),
                    pollensMax: this.createCounter(`stig_counter_${pId}_pollens_total`, this.getFlowerTotalPollens()),
                    jokers: this.createCounter(`stig_counter_${pId}_jokers`, player.jokerUsed ? 0:1),
                    actions: this.createCounter(`stig_counter_${pId}_actions`, player.npad),
                    actionsMax: this.createCounter(`stig_counter_${pId}_actions_total`, this.gamedatas.turn),
                    unlockedActions: this.createCounter(`stig_counter_${pId}_unlockedActions`, player.ua),
                    lockedActions: this.createCounter(`stig_counter_${pId}_lockedActions`, player.la),
                };
                this.updateTurnMarker(pId,this.gamedatas.turn,player.npad+1);
                // Useful to order boards
                nPlayers++;
                if (isCurrent) currentPlayerNo = player.no;
            });
    
            // Order them
            this.forEachPlayer((player) => {
                let isCurrent = player.id == this.player_id;
                //let 3 spaces for personal board, central board and schema board
                let order = ((player.no - currentPlayerNo + nPlayers) % nPlayers) + 3;
                if (isCurrent) order = 1;
                $(`stig_player_board_container_wrapper_${player.id}`).style.order = order;
            });
    
            this.updateFirstPlayer();
        },
        updateFirstPlayer() {
            let pId = this.gamedatas.firstPlayer;
            debug("updateFirstPlayer()",pId);
            if(pId == null) return;
            let divHolder = $(`overall_player_board_${pId}`).querySelector('.stig_first_player_holder');
            if(!$(`stig_first_player`) ){
                dojo.place('<div id="stig_first_player"></div>', divHolder);
                this.addTooltip('stig_first_player', _('Starting player'),'');
            }
            this.slide('stig_first_player',divHolder, {
                phantom: false,
            }).then(() => this.adaptPlayersPanels() );
            
        },
            
        /**
         * Player panel
         */

        tplPlayerPanel(player) {
            return `<div class='stig_panel'>
            <div class="stig_first_player_holder"></div>
            <div class='stig_player_infos'>
                ${this.tplResourceCounter(player, 'tokens_deck')}
                ${this.tplResourceCounter(player, 'actions', 0, this.gamedatas.turn)}
                ${this.tplResourceCounter(player, 'tokens_recruit',3)}
                ${this.tplResourceCounter(player, 'pollens',9, this.getFlowerTotalPollens())}
                ${this.tplResourceCounter(player, 'unlockedActions')}
                ${this.tplResourceCounter(player, 'lockedActions')}
                ${this.gamedatas.jokerMode>0 ? this.tplResourceCounter(player, 'jokers') :''}
            </div>
            </div>`;
        },
            
        /**
         * Use this tpl for any counters that represent qty of tokens
         */
        tplResourceCounter(player, res, nbSubIcons = null, totalValue = null) {
            let totalText = totalValue ==null ? '' : `<span id='stig_counter_${player.id}_${res}_total' class='stig_resource_${res}_total'></span> `;
            return `
            <div class='stig_player_resource stig_resource_${res}'>
                <span id='stig_counter_${player.id}_${res}' 
                class='stig_resource_${res}'></span>${totalText}${this.formatIcon(res, nbSubIcons)}
                <div class='stig_reserve' id='stig_reserve_${player.id}_${res}'></div>
            </div>
            `;
        },
          
        ////////////////////////////////////////////////////////  
        //    ____                      _     
        //   |  _ \                    | |    
        //   | |_) | ___   __ _ _ __ __| |___ 
        //   |  _ < / _ \ / _` | '__/ _` / __|
        //   | |_) | (_) | (_| | | | (_| \__ \
        //   |____/ \___/ \__,_|_|  \__,_|___/
        //                                    
        //        
        ////////////////////////////////////////////////////////
        tplPlayerBoard(player) {
            let turn = this.gamedatas.turn;
            //We want to display the marker before the player take the action
            let turnActions = Math.min(turn,player.npad + 1);
            let flowerType = this.getFlowerType();
            return `<div class='stig_resizable_board' id='stig_player_board_container_wrapper_${player.id}' data_player='${player.id}'>
            <div class='stig_player_board_container'>
                <div class="stig_player_board" id='stig_player_board_${player.id}' data_flower_type="${flowerType}">
                    <div class='player-name' style='color:#${player.color};'>${player.name}</div>
                    ${this.tplTurnMarkerContainer({ 'player_id':player.id ,'turn':turn, 'turnActions':turnActions, init:true})}
                    <div class="stig_newturn_markers" id="stig_newturn_markers_${player.id}" >
                    </div>
                    ${this.tplWindDirContainer({'player_id':player.id })}
                    <div id="stig_recruits_${player.id}" class='stig_recruits'>
                    </div>
                    <div id="stig_grid_${player.id}" class='stig_grid'>
                    </div>
                    <div id="stig_grid_out_${player.id}" class='stig_grid_out'>
                    </div>
                </div>
            </div>
            </div>`;
        },
        setupCentralBoard(){
            if(this.gamedatas.nocb == true) return;
            debug("setupCentralBoard");
            this.place('tplCentralBoard',{}, 'stig_player_boards');
        },
        setupSchemaBoard(){
            debug("setupSchemaBoard");
            let schema = this.gamedatas.schemas[this.gamedatas.schema];
            this.place('tplSchemaBoard', schema, 'stig_player_boards');
            let grid = `stig_grid_schema_${schema.id}`;
            let k = 0;
            schema.end.forEach((token) => {
                k++;
                //These are not real tokens with id
                token.id = k;
                let elt = this.addToken(token,grid,'_virtual');
                elt.classList.add('stig_schema_token');
            });
        },
        tplSchemaBoard(schema) {
            schema.name = _('Targeted schema');
            let stars = '';
            let k = 0;
            while(k< schema.difficulty){ stars += `<i class="stig_difficulty_star"></i>`; k++;}
            return `<div class='stig_resizable_board' id='stig_schema_board_container_wrapper' data_schema='${schema.id}'>
            <div class='stig_schema_board_container'>
                <div class="stig_schema_board" id='stig_schema_board_${schema.id}' data_flower_type="${schema.type}">
                    <div class='stig_schema_name'>${schema.name}</div>
                    <div id="stig_grid_schema_${schema.id}" class='stig_grid'>
                    </div>
                    <div class='stig_schema_difficulty'>&nbsp;&nbsp;${stars}&nbsp;&nbsp;</div>
                    <div class='stig_schema_number'>&nbsp;&nbsp;&nbsp;&nbsp;${schema.id}&nbsp;&nbsp;&nbsp;&nbsp;</div>
                </div>
            </div>
            </div>`;
        },
        
        tplCentralBoard() {
            let boardName = _('StigmaReine (Central board)');
            let flowerType = this.getFlowerType();
            return `<div class='stig_resizable_board' id='stig_central_board_container_wrapper'>
            <div class='stig_central_board_container'>
                <div class="stig_central_board" id='stig_central_board' data_flower_type="${flowerType}">
                    <div class='stig_schema_name'>${boardName}</div>
                    ${this.tplWindDirContainer({'player_id':'central' })}
                    <div id="stig_recruits_central" class='stig_recruits'></div>
                    <div id="stig_grid_central" class='stig_grid'>
                    </div>
                    <div id="stig_grid_out_central" class='stig_grid_out'>
                    </div>
                </div>
            </div>
            </div>`;
        },

        ////////////////////////////////////////////////////////
        //    _______    _                  
        //   |__   __|  | |                 
        //      | | ___ | | _____ _ __  ___ 
        //      | |/ _ \| |/ / _ \ '_ \/ __|
        //      | | (_) |   <  __/ | | \__ \
        //      |_|\___/|_|\_\___|_| |_|___/
        //                           
        ////////////////////////////////////////////////////////   
        updateTurnMarker(player_id, turn, action) {
            debug('updateTurnMarker', player_id, turn, action);
            this.gamedatas.turn = turn;
            //FORCE positive value (in case of unexpected behavior Or testing in god mode)
            let newturn = Math.min(TURN_MAX,this.gamedatas.turn);
            newturn = Math.max(1,newturn);
            let newcount_actions = Math.min(newturn + 1,action);
            newcount_actions = Math.max(1,newcount_actions);
            let existingMarker = $(`stig_turn_marker_${player_id}`);
            let previousContainer = null;
            let newTurnContainer = this.addTurnMarker(player_id,newturn,newcount_actions);
            if(! existingMarker ) existingMarker = this.place('tplTurnMarker', player_id, newTurnContainer );
            else previousContainer = existingMarker.parentElement;
            if(previousContainer != newTurnContainer) {
                this.slide(existingMarker, newTurnContainer, {} ).then( () => {
                    dojo.destroy(previousContainer);
                });
            }
                
            let k =0;
            while(k < (turn - TURN_MAX) ){
                k++;
                let token = {id: `newTurn_${player_id}_${k}`, player_id: player_id, type: TOKEN_TYPE_NEWTURN };
                let divId = `stig_token_${token.id}`;
                if(!$(`${divId}`)){
                    this.addToken(token, this.getVisibleTitleContainer());
                    let div = $(`${divId}`);
                    div.dataset.turn = turn;
                    div.classList.add('stig_newturn_marker');
                    //remove common class to Avoid batch deletion :
                    div.classList.remove('stig_token');
                    this.slide(div, $(`stig_newturn_markers_${token.player_id}` ), { duration: 10 } );
                }
            }
        },    
        tplTurnMarkerContainer(datas) {
            //FORCE positive value (in case of unexpected behavior Or testing in god mode)
            datas.turn = Math.min(TURN_MAX,datas.turn);
            datas.turn = Math.max(1,datas.turn);
            datas.turnActions = Math.min(datas.turn + 1,datas.turnActions);
            datas.turnActions = Math.max(1,datas.turnActions);
            let marker = datas.init ? this.tplTurnMarker(datas.player_id) : '';
            return `<div class="stig_turn_marker_container" data-player_id="${datas.player_id}" data-turn="${datas.turn}" data-count_actions="${datas.turnActions}">${marker}</div>`;
        },
        tplTurnMarker(player_id) {
            return `<div class="stig_turn_marker" id="stig_turn_marker_${player_id}"></div>`;
        },
        addTurnMarker(player_id,turn,turnActions) {
            debug("addTurnMarker",player_id,turn,turnActions);
            let playerBoard = $(`stig_player_board_${player_id}`);
            let container = playerBoard.querySelector(`.stig_turn_marker_container[data-player_id='${player_id}'][data-turn='${turn}'][data-count_actions='${turnActions}']`);
            if(container !=null) return container;

            let elt = this.place('tplTurnMarkerContainer', { 'player_id':player_id ,'turn':turn, 'turnActions':turnActions}, playerBoard );
            return elt;
        },
        addSelectableTokenCell(player_id, row, column) {
            debug("addSelectableTokenCell",player_id, row, column);
            let playerGrid = $(`stig_grid_${player_id}`);
            let tokenDivId = `stig_token_cell_${player_id}_${row}_${column}`;
            if ( $(tokenDivId) ) return $(tokenDivId);
            
            let token = this.place('tplTokenCell', {player_id:player_id, row:row, column:column}, playerGrid);
    
            debug("addSelectableTokenCell() result=> ",token);
            return token;
        },
        tplTokenCell(token) {
            return `<div class="stig_token_cell" id="stig_token_cell_${token.player_id}_${token.row}_${token.column}" data-row="${token.row}" data-col="${token.column}"></div>`;
        },
        addToken(token, location = null, divIdSuffix = '') {
            debug("addToken",token, location,divIdSuffix);
            let tokenDivId = `stig_token_${token.id}${divIdSuffix}`;
            if ( $(tokenDivId) ) return $(tokenDivId);
            
            token.divIdSuffix = (divIdSuffix == undefined) ? '' : divIdSuffix;
            let elt = this.place('tplToken', token, location == null ? this.getTokenContainer(token) : location);
            return elt;
        },
        tplToken(token) {
            return `<div class="stig_token" id="stig_token_${token.id}${token.divIdSuffix}" data-id="${token.id}" data-player_id="${token.pId}" data-type="${token.type}" data-state="${token.state}" data-row="${token.row}" data-col="${token.col}"></div>`;
        },   
        tplWindDirContainer(datas) {
            let winds = '';
            for(let k=1; k< TURN_MAX;k++){
                winds+= `<div id="stig_wind_dir_${datas.player_id}_${k}" class='stig_wind_dir' data-turn="${k}"></div>`;
            }
            return `<div id="stig_wind_dir_container_${datas.player_id}" class='stig_wind_dir_container'>${winds}</div>`;
        },   
        setupTokens() {
            debug("setupTokens");
            let tokenIds = this.gamedatas.tokens.map((token) => {
                if (!$(`stig_token_${token.id}`)) {
                    this.addToken(token);
                }
      
                let o = $(`stig_token_${token.id}`);
                if (!o) return null;
        
                let container = this.getTokenContainer(token);
                if (o.parentNode != $(container)) {
                    dojo.place(o, container);
                }
                o.dataset.state = token.state;
                o.dataset.type = token.type;
                o.dataset.row = token.row;
                o.dataset.col = token.col;
        
                return token.id;
            });
            //Clean obsolete tokens:
            document.querySelectorAll('.stig_token').forEach((oToken) => {
                if (!tokenIds.includes(parseInt(oToken.getAttribute('data-id')))
                    && !oToken.classList.contains('stig_schema_token')
                ) {
                    dojo.destroy(oToken);
                }
            });
        },
        getTokenContainer(token) {
            debug("getTokenContainer",token);
            let playerboard_id = token.pId>0 ? token.pId :'central';
            if (token.location == 'central_board') {
                let tokenHolder = this.addSelectableTokenCell(playerboard_id,token.row, token.col);
                if( tokenHolder){
                    tokenHolder.classList.add('stig_token_holder');
                    return tokenHolder.id;
                }
                return $(`stig_grid_central`);
            }
            else if (token.location == 'player_board') {
                let tokenHolder = this.addSelectableTokenCell(playerboard_id,token.row, token.col);
                if( tokenHolder){
                    tokenHolder.classList.add('stig_token_holder');
                    return tokenHolder.id;
                }
                return $(`stig_grid_${token.pId}`);
            }
            else if (token.location == 'OUT') {
                //IF row/col out of grid (after wind for example, don't show it ? )
                return $(`stig_grid_out_${playerboard_id}`);
            }
            else if (token.location == 'player_recruit') {
                let recruitTypeZone = `stig_recruits_${token.pId}_${token.type}`;
                if(! $(`${recruitTypeZone}`)){
                    dojo.place(`<div id=${recruitTypeZone} data-type=${token.type} class='stig_recruits_type'></div>`, `stig_recruits_${token.pId}`);
                }
                return $(`${recruitTypeZone}`);
            }
            else if (token.location == 'central_recruit') {
                let recruitTypeZone = `stig_recruits_central_${token.type}`;
                if(! $(`${recruitTypeZone}`)){
                    dojo.place(`<div id=${recruitTypeZone} data-type=${token.type} class='stig_recruits_type'></div>`, `stig_recruits_central`);
                }
                return $(`${recruitTypeZone}`);
            }
            else if(token.location == 'central_toplace'){
                return $(`stig_select_piece_container`);
            }
            console.error('Trying to get container of a token', token);
            return 'game_play_area';
          },
        //Direct selection of a cell, independent of a token
        initCellSelection(actionName, possiblePlaces, playerBoardId = 'central',newType = null,
            confirmMessage = null    
        ){
            debug( 'initCellSelection() ', actionName, possiblePlaces,playerBoardId,newType );
            let playerBoard = null;
            if(playerBoardId =='central') {
                playerBoard = $(`stig_central_board`);
            }
            else {
                playerBoard = $(`stig_player_board_${playerBoardId}`);
            }
            
            //possible places to play :
            Object.values(possiblePlaces).forEach((coord) => {
                let row = coord.row;
                let column = coord.col;
                let elt2 = this.addSelectableTokenCell(playerBoardId,row, column);
                elt2.dataset.type = newType;
                this.onClick(`${elt2.id}`, (evt) => {
                    //CLICK SELECT DESTINATION
                    playerBoard.querySelectorAll('.stig_token_cell:not(.stig_token_holder)').forEach((oToken) => {
                        oToken.classList.remove('selected');
                    });
                    let div = evt.target;
                    div.classList.add('selected');
                    $(`btnConfirm`).classList.remove('disabled');
                });
            });
            this.addPrimaryActionButton('btnConfirm', _('Confirm'), () => {
                let selectedTokenCell = playerBoard.querySelector(`.stig_token_cell.selected`);
                if(confirmMessage) {
                    this.confirmationDialog(confirmMessage, () => {
                        this.takeAction(actionName, { row: selectedTokenCell.dataset.row, col:selectedTokenCell.dataset.col, });
                    });
                }
                else {
                    this.takeAction(actionName, { row: selectedTokenCell.dataset.row, col:selectedTokenCell.dataset.col, });
                }
            }); 
            //DISABLED by default
            $(`btnConfirm`).classList.add('disabled');
        },
        
        //Direct selection of a token, independent of any other
        initTokenSimpleSelection(actionName, tokensIds){
            debug( 'initTokenSimpleSelection() ', actionName, tokensIds );
            //possible places to play :
            Object.values(tokensIds).forEach((tokensId) => {
                let elt = $(`stig_token_${tokensId}`);
                this.onClick(elt, (evt) => {
                    let div = evt.target;
                    document.querySelectorAll('.stig_token').forEach((oToken) => {
                        oToken.classList.remove('selected');
                    });
                    div.classList.add('selected');
                    $(`btnConfirm`).classList.remove('disabled');
                });
            });
            this.addPrimaryActionButton('btnConfirm', _('Confirm'), () => {
                let selectedToken = document.querySelector(`.stig_token.selected`);
                this.takeAction(actionName, { tokenId: selectedToken.dataset.id,});
            }); 
            //DISABLED by default
            $(`btnConfirm`).classList.add('disabled');
        },
        initTokenSelectionDest: function(actionName,possibleMoves, playerBoardId = 'central', 
            actionOutName = null, differentType = null
            ){
            debug( 'initTokenSelectionDest() ', actionName, possibleMoves,playerBoardId,actionOutName, differentType);
            let playerBoard = null;
            if(playerBoardId =='central') {
                playerBoard = $(`stig_central_board`);
            }
            else {
                playerBoard = $(`stig_player_board_${playerBoardId}`);
            }

            Object.keys(possibleMoves).forEach((tokenId) => {
                let coords = possibleMoves[tokenId];
                if (coords.length == 0) return;
                //Click token origin
                this.onClick(`stig_token_${tokenId}`, (evt) => {
                    [...playerBoard.querySelectorAll('.stig_token')].forEach((o) => o.classList.remove('selected'));
                    let div = evt.target;
                    div.classList.toggle('selected');
                    [...playerBoard.querySelectorAll('.stig_token_cell:not(.stig_token_holder)')].forEach((o) => {
                        dojo.destroy(o);
                        });
                    //disable confirm while we don't know destination
                    $(`btnConfirm`).classList.add('disabled');
                    if($(`btnMoveOut`)) $(`btnMoveOut`).classList.add('disabled');
                    Object.values(possibleMoves[tokenId]).forEach((coord) => {
                        if(coord.out){
                            $(`btnMoveOut`).classList.remove('disabled');
                            return;
                        }
                        let row = coord.y !=null ? coord.y: coord.row;
                        let column = coord.x !=null ? coord.x: coord.col;
                        let elt2 = this.addSelectableTokenCell(playerBoardId,row, column);
                        elt2.dataset.type = differentType ==null ? div.dataset.type : differentType;
                        //Click token destination :
                        this.onClick(`${elt2.id}`, (evt) => {
                            [...playerBoard.querySelectorAll('.stig_token_cell:not(.stig_token_holder)')].forEach((o) => {
                                o.classList.remove('selected');
                                });
                            let div = evt.target;
                            div.classList.toggle('selected');
                            $(`btnConfirm`).classList.remove('disabled');
                        });
                    });
                });
            });
            this.addPrimaryActionButton('btnConfirm', _('Confirm'), () => {
                let selectedToken = playerBoard.querySelector(`.stig_token.selected`);
                let selectedTokenCell = playerBoard.querySelector(`.stig_token_cell:not(.stig_token_holder).selected`);
                this.takeAction(actionName, { tokenId: selectedToken.dataset.id,  row: selectedTokenCell.dataset.row, col:selectedTokenCell.dataset.col, });
            }); 
            //DISABLED by default
            $(`btnConfirm`).classList.add('disabled');
            if(actionOutName){
                this.addPrimaryActionButton('btnMoveOut',  _('Move out'), () => {
                    this.confirmationDialog(_("Are you sure to move this token out of the board ?"), () => {
                        let selectedToken = playerBoard.querySelector(`.stig_token.selected`);
                        this.takeAction(actionOutName, { tokenId: selectedToken.dataset.id, });
                    });
                });
                $(`btnMoveOut`).classList.add('disabled');
            }
        },
        reinitTokensSelection: function(possibleTokens){
            debug( 'reinitTokensSelection() ', possibleTokens );
            //UNSELECT
            this.currentToken1 = null;
            this.currentToken2 = null;
            [...document.querySelectorAll(`.stig_token`)].forEach((o) => {
                o.classList.remove('selected');
                o.classList.remove('selectable');
                if(o.dataset.type_origin) o.dataset.type = o.dataset.type_origin;
            });
            [...document.querySelectorAll(`.stig_token_holder`)].forEach((o) => {
                o.classList.remove('selected');
                o.classList.remove('selectable');
                if(o.dataset.type_origin) o.dataset.type = o.dataset.type_origin;
            });
            //REINIT SELECTION
            Object.keys(possibleTokens).forEach((tokenId3) => {
                $(`stig_token_${tokenId3}`).classList.add('selectable');
            });
            $(`btnConfirm`).classList.add('disabled');
        },
        initMultiTokenSelection: function(actionName,possibleTokens, callbackSelectionDone = null){
            debug( 'initMultiTokenSelection() ', actionName, possibleTokens );
           
            this.currentToken1 = null;
            this.currentToken2 = null;
            
            this.addPrimaryActionButton('btnConfirm',  _('Confirm'), () => { 
                this.takeAction(actionName, {t1: this.currentToken1, t2: this.currentToken2}); 
            } );
            //DISABLED by default
            $(`btnConfirm`).classList.add('disabled');
            let playerBoard = $(`stig_player_board_${this.player_id}`);
            Object.keys(possibleTokens).forEach((tokenId) => {
                //Click token 1
                this.onClick(`stig_token_${tokenId}`, (evt) => {
                    let tokenIdInt = parseInt(tokenId);
                    let div = evt.target;
                    $(`btnConfirm`).classList.add('disabled');
                    if(div.classList.contains('selected')){
                        //UNSELECT
                        this.currentToken1 = null;
                        this.currentToken2 = null;
                        [...playerBoard.querySelectorAll(`.stig_token:not(#stig_token_${this.currentToken1}):not(#stig_token_${this.currentToken2})`)].forEach((o) => {
                            o.classList.remove('selected');
                        });
                        //REINIT SELECTION
                        Object.keys(possibleTokens).forEach((tokenId3) => {
                            $(`stig_token_${tokenId3}`).classList.add('selectable');
                        });
                    }
                    else if(!this.currentToken1){
                        //SELECT 1
                        this.currentToken1 = tokenIdInt;
                        div.classList.add('selected');
                        
                        [...playerBoard.querySelectorAll(`.stig_token:not(#stig_token_${this.currentToken1}):not(#stig_token_${this.currentToken2})`)].forEach((o) => {
                            o.classList.remove('selectable');
                        });
                        Object.values(possibleTokens[tokenIdInt]).forEach((tokenId2) => {
                            $(`stig_token_${tokenId2}`).classList.add('selectable');
                        });
                    }
                    else if(!this.currentToken2 && possibleTokens[tokenIdInt].includes(this.currentToken1)){
                        //SELECT 2
                        this.currentToken2 = tokenIdInt;
                        div.classList.add('selected');
                        $(`btnConfirm`).classList.remove('disabled');
                        
                        [...playerBoard.querySelectorAll(`.stig_token:not(#stig_token_${this.currentToken1}):not(#stig_token_${this.currentToken2})`)].forEach((o) => {
                            o.classList.remove('selectable');
                        });
                    }
                    if(callbackSelectionDone !=null){
                        callbackSelectionDone(this.currentToken1,this.currentToken2);
                    }

                });
            });
           
        },

        removeEmptyCellHolders: function()
        {
            //Remove parasites : token holders with no tokens (maybe after wind or moves that didn't clean it)
            $(`stig_player_boards`).querySelectorAll(`.stig_token_cell.stig_token_holder:empty`).forEach((a) => { dojo.destroy(a); }); 
        },
        ////////////////////////////////////////////////////////////
        // _____                          _   _   _
        // |  ___|__  _ __ _ __ ___   __ _| |_| |_(_)_ __   __ _
        // | |_ / _ \| '__| '_ ` _ \ / _` | __| __| | '_ \ / _` |
        // |  _| (_) | |  | | | | | | (_| | |_| |_| | | | | (_| |
        // |_|  \___/|_|  |_| |_| |_|\__,_|\__|\__|_|_| |_|\__, |
        //                                                 |___/
        ////////////////////////////////////////////////////////////
        /**
         * Format log strings
         *  @Override
         */
        format_string_recursive(log, args) {
            try {
            if (log && args && !args.processed) {
                args.processed = true;

                log = this.formatString(_(log));
                let token_color = 'token_color';
                let token_type = 'token_type';
                if(token_color in args && token_type in args) {
                    args.token_color = this.formatIcon("token_log",args.token_type,args.token_type);
                    args.token_type = "";
                }
                let token_color2 = 'token_color2';
                let token_type2 = 'token_type2';
                if(token_color2 in args && token_type2 in args) {
                    args.token_color2 = this.formatIcon("token_log",args.token_type2,args.token_type2);
                    args.token_type2 = "";
                }

            }
            } catch (e) {
                console.error(log, args, 'Exception thrown', e.stack);
            }

            return this.inherited(arguments);
        },
            
        formatString(str) {
            return str;
        },
        /**
         * Replace some expressions by corresponding html formating
         */
        formatIcon(name, nbSubIcons = null, filterSubIconType = null, n = null) {
            let type = name;
            let tplSubIcons ='';
            if(nbSubIcons && nbSubIcons > 0){
                for(let k = 1; k<=nbSubIcons; k++){
                    if(filterSubIconType != null && k!= filterSubIconType) continue;
                    tplSubIcons +=`<div class='stig_subicon_${type}' data-type='${k}'></div>`;
                }
            }
            let text = n == null ? '' : `<span>${n}</span>`;
            return `<div class="stig_icon_container stig_icon_container_${type}">
                <div class="stig_icon stig_${type}">${text}${tplSubIcons}</div>
                </div>`;
        },
        ////////////////////////////////////////////////////////
        //  ___        __         ____                  _
        // |_ _|_ __  / _| ___   |  _ \ __ _ _ __   ___| |
        //  | || '_ \| |_ / _ \  | |_) / _` | '_ \ / _ \ |
        //  | || | | |  _| (_) | |  __/ (_| | | | |  __/ |
        // |___|_| |_|_|  \___/  |_|   \__,_|_| |_|\___|_|
        ////////////////////////////////////////////////////////

        updatePlayerOrdering() {
            debug("updatePlayerOrdering");
            this.inherited(arguments);
            dojo.place('player_board_config', 'player_boards', 'first');
        },
        setupInfoPanel() {
            debug("setupInfoPanel");
            
            dojo.place(this.tplConfigPlayerBoard(), 'player_boards', 'first');
            this._counters['turn'] = this.createCounter('stig_counter_turn',1);
            
            this._settingsModal = new customgame.modal('showSettings', {
                class: 'stig_popin',
                closeIcon: 'fa-times',
                title: _('Settings'),
                closeAction: 'hide',
                verticalAlign: 'flex-start',
                contentsTpl: `<div id='stig_settings'>
                    <div id='stig_settings_header'></div>
                    <div id="settings-controls-container"></div>
                </div>`,
            });
        },
        
        tplConfigPlayerBoard() {
            let turn = this.gamedatas.turn;
            let turnMax = TURN_MAX;
            return `
            <div class='player-board' id="player_board_config">
                <div id="player_config" class="player_board_content">
                <div class="player_config_row" id="turn_counter_wrapper">
                  ${_('Turn')} <span id='stig_counter_turn'>${turn}</span> / <span id='stig_counter_turn_max'>${turnMax}</span>
                </div>
                <div class="player_config_row">
                    <div id="show-settings">
                    <svg  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                        <g>
                        <path class="fa-secondary" fill="currentColor" d="M638.41 387a12.34 12.34 0 0 0-12.2-10.3h-16.5a86.33 86.33 0 0 0-15.9-27.4L602 335a12.42 12.42 0 0 0-2.8-15.7 110.5 110.5 0 0 0-32.1-18.6 12.36 12.36 0 0 0-15.1 5.4l-8.2 14.3a88.86 88.86 0 0 0-31.7 0l-8.2-14.3a12.36 12.36 0 0 0-15.1-5.4 111.83 111.83 0 0 0-32.1 18.6 12.3 12.3 0 0 0-2.8 15.7l8.2 14.3a86.33 86.33 0 0 0-15.9 27.4h-16.5a12.43 12.43 0 0 0-12.2 10.4 112.66 112.66 0 0 0 0 37.1 12.34 12.34 0 0 0 12.2 10.3h16.5a86.33 86.33 0 0 0 15.9 27.4l-8.2 14.3a12.42 12.42 0 0 0 2.8 15.7 110.5 110.5 0 0 0 32.1 18.6 12.36 12.36 0 0 0 15.1-5.4l8.2-14.3a88.86 88.86 0 0 0 31.7 0l8.2 14.3a12.36 12.36 0 0 0 15.1 5.4 111.83 111.83 0 0 0 32.1-18.6 12.3 12.3 0 0 0 2.8-15.7l-8.2-14.3a86.33 86.33 0 0 0 15.9-27.4h16.5a12.43 12.43 0 0 0 12.2-10.4 112.66 112.66 0 0 0 .01-37.1zm-136.8 44.9c-29.6-38.5 14.3-82.4 52.8-52.8 29.59 38.49-14.3 82.39-52.8 52.79zm136.8-343.8a12.34 12.34 0 0 0-12.2-10.3h-16.5a86.33 86.33 0 0 0-15.9-27.4l8.2-14.3a12.42 12.42 0 0 0-2.8-15.7 110.5 110.5 0 0 0-32.1-18.6A12.36 12.36 0 0 0 552 7.19l-8.2 14.3a88.86 88.86 0 0 0-31.7 0l-8.2-14.3a12.36 12.36 0 0 0-15.1-5.4 111.83 111.83 0 0 0-32.1 18.6 12.3 12.3 0 0 0-2.8 15.7l8.2 14.3a86.33 86.33 0 0 0-15.9 27.4h-16.5a12.43 12.43 0 0 0-12.2 10.4 112.66 112.66 0 0 0 0 37.1 12.34 12.34 0 0 0 12.2 10.3h16.5a86.33 86.33 0 0 0 15.9 27.4l-8.2 14.3a12.42 12.42 0 0 0 2.8 15.7 110.5 110.5 0 0 0 32.1 18.6 12.36 12.36 0 0 0 15.1-5.4l8.2-14.3a88.86 88.86 0 0 0 31.7 0l8.2 14.3a12.36 12.36 0 0 0 15.1 5.4 111.83 111.83 0 0 0 32.1-18.6 12.3 12.3 0 0 0 2.8-15.7l-8.2-14.3a86.33 86.33 0 0 0 15.9-27.4h16.5a12.43 12.43 0 0 0 12.2-10.4 112.66 112.66 0 0 0 .01-37.1zm-136.8 45c-29.6-38.5 14.3-82.5 52.8-52.8 29.59 38.49-14.3 82.39-52.8 52.79z" opacity="0.4"></path>
                        <path class="fa-primary" fill="currentColor" d="M420 303.79L386.31 287a173.78 173.78 0 0 0 0-63.5l33.7-16.8c10.1-5.9 14-18.2 10-29.1-8.9-24.2-25.9-46.4-42.1-65.8a23.93 23.93 0 0 0-30.3-5.3l-29.1 16.8a173.66 173.66 0 0 0-54.9-31.7V58a24 24 0 0 0-20-23.6 228.06 228.06 0 0 0-76 .1A23.82 23.82 0 0 0 158 58v33.7a171.78 171.78 0 0 0-54.9 31.7L74 106.59a23.91 23.91 0 0 0-30.3 5.3c-16.2 19.4-33.3 41.6-42.2 65.8a23.84 23.84 0 0 0 10.5 29l33.3 16.9a173.24 173.24 0 0 0 0 63.4L12 303.79a24.13 24.13 0 0 0-10.5 29.1c8.9 24.1 26 46.3 42.2 65.7a23.93 23.93 0 0 0 30.3 5.3l29.1-16.7a173.66 173.66 0 0 0 54.9 31.7v33.6a24 24 0 0 0 20 23.6 224.88 224.88 0 0 0 75.9 0 23.93 23.93 0 0 0 19.7-23.6v-33.6a171.78 171.78 0 0 0 54.9-31.7l29.1 16.8a23.91 23.91 0 0 0 30.3-5.3c16.2-19.4 33.7-41.6 42.6-65.8a24 24 0 0 0-10.5-29.1zm-151.3 4.3c-77 59.2-164.9-28.7-105.7-105.7 77-59.2 164.91 28.7 105.71 105.7z"></path>
                        </g>
                    </svg>
                    </div>
                </div>
            </div>
            `;
        },
  
   });             
});
//# sourceURL=stigmeria.js