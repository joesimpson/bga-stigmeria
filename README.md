# bga-stigmeria
Board game adaptation of "Stigméria" for Board Game Arena website.

This code has been produced on the BGA studio platform for use on http://boardgamearena.com.

# License
License file is [LICENCE_BGA](/LICENCE_BGA).

# Game design
This board game is designed by Gabriel Souleyre, published by Editions Garajeux.

All images copyright to their artists : 
David Cochard,
Lisa Fix

# BGG
Link : https://boardgamegeek.com/boardgame/381860/stigmeria

# SCSS

Css file is compiled by VSCode extension 'LiveSass Compiler' with these settings :
```
    "liveSassCompile.settings.formats": [
        {
            "format": "expanded",
            "extensionName": ".css",
            "savePath": null,
            "savePathReplacementPairs": null
        }
    ],  
    "liveSassCompile.settings.includeItems": [
        "/stigmeria.scss",
    ],
```

# BGA Options / Preferences format

Written in PHP with many constants.
Read by the game at setup and later by Preferences module.
Erased in JSON by BGA commit/build, so we keep the php in modules/php

So we can easily regenerate the JSON version (now included in the workspace) from PHP version with these steps :

- call the tchat debug function `debugJSON()`
- then browser inspect the notif and copy its DOM content. 
- then copy this JSON to the json file 
- send the json file to distant BGA folder via FTP
- Manage game : reload options (this will use the php options version)

