# Jogada

**Jogada** is a simple, self-hosted, PHP-based server for turn based games.
Due to the turn based nature of the game, it does require any daemon running in the background. Any old server with a somewhat recent PHP version will do.

## Usage

### Setting data
Send a POST request to the game endpoint to create a new match of your gametype 
```
TODO
```

Send a POST request to the turn endpoint to append a new turn to the match
```
TODO
```

Send a POST request to the addplayer endpoint to add a new player to the match
```
TODO
```

### Getting data
Send a GET request with the game id to get a summary of the game
```
TODO
```

Send a GET request to the turns endpoint to get a list of all turns
```
TODO
```


## Use for your own games

Every game requires a _parser_ to check valid turns, victory conditions etc.
Parsers need to implement the `iGame` interface.

An example of a [parser for a simple Tic-Tac-Toe game](engine/TicTacToeGame.php) can be found in the repository.
