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
    const ACTION_TYPE_MERGE = 10;
    const ACTION_TYPE_DIAGONAL = 14;
    const ACTION_TYPE_SWAP = 15;
    const ACTION_TYPE_MOVE_FAST = 16;
    const ACTION_TYPE_WHITE = 20;

    const TOKEN_STIG_WHITE =    8;
    const TOKEN_TYPE_NEWTURN = 21;

    return declare("bgagame.stigmeria", [customgame.game], {
        constructor: function(){
            console.log('stigmeria constructor');
              
            // Fix mobile viewport (remove CSS zoom)
            this.default_viewport = 'width=800';

            this._counters = {};

            this._notifications = [
                ['newRound', 10],
                ['newWinds', 10],
                ['newTurn', 800],
                ['updateFirstPlayer', 500],
                ['useActions', 500],
                ['drawToken', 900],
                ['moveToCentralBoard', 900],
                ['moveOnCentralBoard', 900],
                ['letNextPlay', 10],
                ['moveToPlayerBoard', 900],
                ['moveOnPlayerBoard', 900],
                ['spMerge', 900],
                ['spSwap', 900],
                ['spWhite', 900],
                ['newPollen', 900],
                ['playJoker', 500],
                ['windBlows', 1800],
                ['addPoints', 800],
            ];
            //For now I don't want to spoil my bar when other player plays, and multiactive state change is more complex
            this._displayNotifsOnTop = false;
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
                    this.addPrimaryActionButton('btnCommonDrawAndPlace', 'Draw and Place', () => {
                        this.confirmationDialog(_("Are you sure to draw a token from your bag ?"), () => {
                            this.takeAction('actCommonDrawAndLand', {});
                        });
                    });
                }
                if(possibleActions.includes('actCommonMove')){
                    this.addPrimaryActionButton('btnCommonMove', 'Move', () => this.takeAction('actCommonMove', {}));
                }
            }
            this.addDangerActionButton('btnNext', 'Next', () => this.takeAction('actGoToNext', {}));
        }, 
        onEnteringStateCentralChoiceTokenToLand: function(args)
        {
            debug( 'onEnteringStateCentralChoiceTokenToLand() ', args );
            
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
            
            this.initTokenSelectionDest('actCentralMove', args.p_places_m);
            this.addSecondaryActionButton('btnCancel', 'Cancel', () => this.takeAction('actCancelChoiceTokenToMove', {}));
        }, 
        
        onEnteringStatePersonalBoardTurn: function(args)
        {
            debug( 'onEnteringStatePersonalBoardTurn() ', args );
            
            let nbActions = args.n;
            let possibleActions = args.a;
            if(nbActions>0){
                this.addPrimaryActionButton('btnDraw', 'Recruit', () => { 
                    this.confirmationDialog(_("Are you sure to draw a token from your bag ?"), () => {
                        this.takeAction('actDraw', {});
                    });
                });
                this.addPrimaryActionButton('btnPlace', 'Land', () => this.takeAction('actLand', {}));
                this.addPrimaryActionButton('btnMove', 'Move', () => this.takeAction('actMove', {}));
                    
                this.gamedatas.players[this.player_id].npad = args.done;
                //updated via notif_useActions
                //this.updateTurnMarker(this.player_id,this.gamedatas.turn,args.done +1 );
                    
                if(possibleActions.includes('actSpecial')){
                    this.addImageActionButton('btnSpecialAction', `<div><div class='stig_icon_flower_violet'></div> Special<div class='stig_icon_flower_violet stig_icon_flipped'></div></div>`, () => { this.takeAction('actSpecial', {}); });
                }
            }
            Object.values(args.pj).forEach((tokenColor) => {
                let src = tokenColor.src;
                let dest = tokenColor.dest;
                this.addImageActionButton(`btnJoker_${src}_${dest}`, `<div><div class='stig_qty'>4</div><div class='stig_token' data-type='${src}'></div> <i class="fa6 fa6-arrow-right"></i> <div class='stig_qty'>4</div> <div class='stig_token' data-type='${dest}'></div></div>`, () =>  {
                    this.confirmationDialog(_("This will update tokens in your recruitment zone. You won't be able to replay a Joker in the game !"), () => {
                        this.takeAction('actJoker', {src:src,dest:dest})
                    });
                });
            });
            if(possibleActions.includes('actLetNextPlay')){
                this.addSecondaryActionButton('btnLetNextPlay', 'Start next player', () => {
                    this.confirmationDialog(_("Next player will start their turn, so you will not be able to play VS actions for this turn."), () => {
                        this.takeAction('actLetNextPlay', {}) ;
                    });
                });
            }
            this.addDangerActionButton('btnEndTurn', 'End turn', () => {
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
            
            this.addSecondaryActionButton('btnCancel', 'Return', () => this.takeAction('actCancelChoiceTokenToLand', {}));
            
            let playerBoard = $(`stig_player_board_${this.player_id}`);
            let selectedToken = null;
            Object.values(args.tokens).forEach((token) => {
                let elt = this.addToken(token, $('stig_select_piece_container'), '_tmp');
                this.onClick(`${elt.id}`, () => {
                    //CLICK SELECT TOKEN
                    if (selectedToken) $(`stig_token_${selectedToken}`).classList.remove('selected');
                    selectedToken = token.id + '_tmp';
                    $(`stig_token_${selectedToken}`).classList.add('selected');
                            
                    let selectedTokenCell = null;
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
            
            this.initTokenSelectionDest('actChoiceTokenToMove', args.p_places_m, this.player_id);

            this.addSecondaryActionButton('btnCancel', 'Return', () => this.takeAction('actCancelChoiceTokenToMove', {}));
        }, 
        
        onEnteringStateSpecialAction: function(args)
        {
            debug( 'onEnteringStateSpecialAction() ', args );
            
            let possibleActions = args.a;
            if(possibleActions.includes(ACTION_TYPE_MERGE)){
                this.addPrimaryActionButton('btnStartMerge', 'Merge', () => this.takeAction('actChoiceSpecial', {act:ACTION_TYPE_MERGE}));
            }
            if(possibleActions.includes(ACTION_TYPE_DIAGONAL)){
                this.addPrimaryActionButton('btnStartDiagonal', 'Diagonal', () => this.takeAction('actChoiceSpecial', {act:ACTION_TYPE_DIAGONAL}));
            }
            if(possibleActions.includes(ACTION_TYPE_SWAP)){
                this.addPrimaryActionButton('btnStartSwap', 'Swap', () => this.takeAction('actChoiceSpecial', {act:ACTION_TYPE_SWAP}));
            }
            if(possibleActions.includes(ACTION_TYPE_MOVE_FAST)){
                this.addPrimaryActionButton('btnStartFastMove', 'Fast move', () => this.takeAction('actChoiceSpecial', {act:ACTION_TYPE_MOVE_FAST}));
            }
            if(possibleActions.includes(ACTION_TYPE_WHITE)){
                this.addPrimaryActionButton('btnStartWhite', 'White', () => this.takeAction('actChoiceSpecial', {act:ACTION_TYPE_WHITE}));
            }
            this.addSecondaryActionButton('btnCancel', 'Return', () => this.takeAction('actCancelSpecial', {}));
        }, 
        
        onEnteringStateSpMerge: function(args)
        {
            debug( 'onEnteringStateSpMerge() ', args );

            this.initMultiTokenSelection('actMerge',args.tokens);
            this.addSecondaryActionButton('btnCancel', 'Return', () => this.takeAction('actCancelSpecial', {}));
        }, 
        
        onEnteringStateSpDiagonal: function(args)
        {
            debug( 'onEnteringStateSpDiagonal() ', args );

            this.initTokenSelectionDest('actDiagonal', args.p_places_m, this.player_id);
            this.addSecondaryActionButton('btnCancel', 'Return', () => this.takeAction('actCancelSpecial', {}));
        }, 
        
        onEnteringStateSpSwap: function(args)
        {
            debug( 'onEnteringStateSpSwap() ', args );
            
            this.addSecondaryActionButton('btnCancel', 'Return', () => this.takeAction('actCancelSpecial', {}));
            this.initMultiTokenSelection('actSwap',args.tokens);
        }, 
        
        onEnteringStateSpFastMove: function(args)
        {
            debug( 'onEnteringStateSpFastMove() ', args );

            this.initTokenSelectionDest('actFastMove', args.p_places_m, this.player_id);
            this.addSecondaryActionButton('btnCancel', 'Return', () => this.takeAction('actCancelSpecial', {}));
        }, 
        
        onEnteringStateSpWhite: function(args)
        {
            debug( 'onEnteringStateSpWhite() ', args );

            this.addSecondaryActionButton('btnCancel', 'Return', () => this.takeAction('actCancelSpecial', {}));
            this.addSecondaryActionButton('btnClearSelection', 'Clear selection', () =>  {
                this.reinitTokensSelection(args.tokens);
            });
            this.initMultiTokenSelection('actWhite',args.tokens, (token1,token2) => {});
            
        }, 
        onEnteringStateSpWhiteChoice: function(args)
        {
            debug( 'onEnteringStateSpWhiteChoice() ', args );

            this.addSecondaryActionButton('btnCancel', 'Return', () => this.takeAction('actCancelSpecial', {}));

            //possible places to play :
            Object.values(args.tokensIds).forEach((tokensId) => {
                let elt = $(`stig_token_${tokensId}`);
                //elt.classList.add('stig_previous_selected');
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
                this.takeAction('actWhiteChoice', { tokenId: selectedToken.dataset.id,});
            }); 
            //DISABLED by default
            $(`btnConfirm`).classList.add('disabled');
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
        notif_moveToCentralBoard(n) {
            debug('notif_moveToCentralBoard: new token on central board', n);
            let token = n.args.token;
            this.addToken(token, this.getVisibleTitleContainer());
            let div = $(`stig_token_${token.id}`);
            div.dataset.row = token.row;
            div.dataset.col = token.col;
            div.dataset.state = token.state;
            this._counters[n.args.player_id]['tokens_deck'].incValue(-1);
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
        notif_letNextPlay(n) {
            debug('notif_letNextPlay: ', n);
            if($(`btnLetNextPlay`) ) dojo.destroy($(`btnLetNextPlay`));
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
        notif_spMerge(n) {
            debug('notif_spMerge: tokens are merged !', n);
            let token1 = n.args.token1;
            let token2 = n.args.token2;
            let div1 = $(`stig_token_${token1.id}`);
            let div2 = $(`stig_token_${token2.id}`);
            div1.dataset.type = token1.type;
            div2.dataset.type = token2.type;
            this.animationBlink2Times(div1);
            this.animationBlink2Times(div2);
        },
        notif_spSwap(n) {
            debug('notif_spSwap: tokens are swapped', n);
            let token1 = n.args.t1;
            let token2 = n.args.t2;
            let div1 = $(`stig_token_${token1.id}`);
            let div2 = $(`stig_token_${token2.id}`);
            /*
            div1.dataset.type = token1.type;
            div1.dataset.row = token1.row;
            div1.dataset.col = token1.col;
            div2.dataset.type = token2.type;
            div2.dataset.row = token2.row;
            div2.dataset.col = token2.col;
            */
            /*
            div1.dataset.type = token1.type;
            div2.dataset.type = token2.type;
            this.animationBlink2Times(div1);
            this.animationBlink2Times(div2);
            */
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
                this.place('tplPlayerPanel', player, `player_panel_content_${player.color}`, 'after');
                this.place('tplPlayerBoard', player, 'stig_player_boards');
                
                document.querySelectorAll('.stig_icon_container_tokens_recruit').forEach((e) => e.dataset.flower_type = this.getFlowerType());

                this.addTooltip(`stig_reserve_${player.id}_tokens_deck`, _('Tokens in bags'),'');
                this.addTooltip(`stig_reserve_${player.id}_tokens_recruit`, _('Tokens in recruit zone'),'');
                this.addTooltip(`stig_reserve_${player.id}_pollens`, _('Pollens on flower'),'');
                this.addTooltip(`stig_reserve_${player.id}_jokers`, _('Jokers'),'');
                this.addTooltip(`stig_reserve_${player.id}_actions`, _('Actions done'),'');

                let pId = player.id;
                this._counters[pId] = {
                    tokens_recruit: this.createCounter(`stig_counter_${pId}_tokens_recruit`, player.tokens_recruit),
                    tokens_deck: this.createCounter(`stig_counter_${pId}_tokens_deck`, player.tokens_deck),
                    pollens: this.createCounter(`stig_counter_${pId}_pollens`, player.pollens),
                    pollensMax: this.createCounter(`stig_counter_${pId}_pollens_total`, this.getFlowerTotalPollens()),
                    jokers: this.createCounter(`stig_counter_${pId}_jokers`, player.jokerUsed ? 0:1),
                    actions: this.createCounter(`stig_counter_${pId}_actions`, player.npad),
                    actionsMax: this.createCounter(`stig_counter_${pId}_actions_total`, this.gamedatas.turn),
                };
        
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
        
                if (order == 1) {
                    //TODO JSA DISplay first player
                    /*
                    dojo.place('<div id="stig_first_player"></div>', `overall_player_board_${player.id}`);
                    this.addCustomTooltip('stig_first_player', _('First player'));
                    */
                }
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
                    <div class='player-name' style='color:#${player.color}'>${player.name}</div>
                    ${this.tplTurnMarkerContainer({ 'player_id':player.id ,'turn':turn, 'turnActions':turnActions, init:true})}
                    <div class="stig_newturn_markers" id="stig_newturn_markers_${player.id}" >
                    </div>
                    ${this.tplWindDirContainer({'player_id':player.id })}
                    <div id="stig_recruits_${player.id}" class='stig_recruits'>
                    </div>
                    <div id="stig_grid_${player.id}" class='stig_grid'>
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
                    <div id="stig_grid_central" class='stig_grid'>
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
            let newturn = Math.min(TURN_MAX,this.gamedatas.turn);
            let newcount_actions = action;
            let existingMarker = $(`stig_turn_marker_${player_id}`);
            let newTurnContainer = this.addTurnMarker(player_id,newturn,newcount_actions);
            if(! existingMarker ) existingMarker = this.place('tplTurnMarker', player_id, newTurnContainer );
            this.slide(existingMarker, newTurnContainer, {} ).then( () => {});
                
            let k =0;
            while(k < (turn - TURN_MAX) ){
                k++;
                let token = {id: 'newTurn'+k, player_id: player_id, type: TOKEN_TYPE_NEWTURN };
                let divId = `stig_token_${token.id}`;
                if(!$(`${divId}`)){
                    this.addToken(token, this.getVisibleTitleContainer());
                    div = $(`${divId}`);
                    div.dataset.turn = turn;
                    div.classList.add('stig_newturn_marker');
                    this.slide(div, $(`stig_newturn_markers_${token.player_id}` ), { duration: 10 } );
                }
            }
        },    
        tplTurnMarkerContainer(datas) {
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
            if (token.location == 'player_board') {
                let tokenHolder = this.addSelectableTokenCell(playerboard_id,token.row, token.col);
                if( tokenHolder){
                    tokenHolder.classList.add('stig_token_holder');
                    return tokenHolder.id;
                }
                return $(`stig_grid_${token.pId}`);
                //TODO JSA IF row/col out of grid (after wind for example, don't show it there)
            }
            if (token.location == 'player_recruit') {
                let recruitTypeZone = `stig_recruits_${token.pId}_${token.type}`;
                if(! $(`${recruitTypeZone}`)){
                    dojo.place(`<div id=${recruitTypeZone} data-type=${token.type} class='stig_recruits_type'></div>`, `stig_recruits_${token.pId}`);
                }
                return $(`${recruitTypeZone}`);
            }
            console.error('Trying to get container of a token', token);
            return 'game_play_area';
          },
          
        
        initTokenSelectionDest: function(actionName,possibleMoves, playerBoardId = 'central'){
            debug( 'initTokenSelectionDest() ', actionName, possibleMoves );
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
                    Object.values(possibleMoves[tokenId]).forEach((coord) => {
                        let row = coord.y !=null ? coord.y: coord.row;
                        let column = coord.x !=null ? coord.x: coord.col;
                        let elt2 = this.addSelectableTokenCell(playerBoardId,row, column);
                        elt2.dataset.type = div.dataset.type;
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
            
            this.addPrimaryActionButton('btnConfirm', 'Confirm', () => { 
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

        ////////////////////////////////////////////////////////////
        // _____                          _   _   _
        // |  ___|__  _ __ _ __ ___   __ _| |_| |_(_)_ __   __ _
        // | |_ / _ \| '__| '_ ` _ \ / _` | __| __| | '_ \ / _` |
        // |  _| (_) | |  | | | | | | (_| | |_| |_| | | | | (_| |
        // |_|  \___/|_|  |_| |_| |_|\__,_|\__|\__|_|_| |_|\__, |
        //                                                 |___/
        ////////////////////////////////////////////////////////////

        /**
         * Replace some expressions by corresponding html formating
         */
        formatIcon(name, nbSubIcons = null, n = null) {
            let type = name;
            let tplSubIcons ='';
            if(nbSubIcons && nbSubIcons > 0){
                for(let k = 1; k<=nbSubIcons; k++){
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