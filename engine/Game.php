<?php

class Game {
    private $parser;
    private $game;
    private $gameId;
    private $seed;
    private $players;
    private $turns;
    private $lastModified;
    private $loaded = false;

    // TODO: Game Meta: maxplayer (-> Parser)
    function __construct($gameType, $gameId=null, $gameSeed=null) {
        $gameType = strtolower($gameType);

        if (is_null($gameId)) {
            $this->gameId = uniqid();
            $this->game = $gameType;
            $this->lastModified = time();
            $this->players = array();
            $this->seed = $gameSeed;
            $this->turns = array();
            $this->parser = new EmptyGame();
            http_response_code(200);
            return;
        }

        if (is_file("games/".$gameType."/".$gameId.".json")) {
            $data = json_decode(file_get_contents("games/".$gameType."/".$gameId.".json"));
            $this->gameId = $gameId;

            $this->game = $data->game;
            $this->seed = $data->seed;
            $this->lastModified = $data->lastModified;
            $this->loaded = true;

            $classname = $data->game."Game";
            if (class_exists($classname)) {
                $this->parsePlayers($data->players);
                $this->turns = $data->turns;
                $this->parser = new $classname;
                $this->parser->parseTurns($this, $this->turns);
                http_response_code(200);
            } else {
                echo "No parser for ".$data->game." found<br>";
                http_response_code(400);
                return false;
            }
        } else {
            echo "Error loading Game #".$gameId;
            http_response_code(400);
            return false;
        }
    }

    // --- pLayer functions -----------------------------------------------------------------------
    function getPlayers() {
        return $this->players;
    }

    function parsePlayers($players) {
        $this->players = array();
        foreach ($players as $player) {
            $p = new Player($player->name, $player->id);
            $this->players[] = $p;
        }
    }

    function nextPlayer() {
        $turnCount = count($this->turns);
        $playerCount = count($this->players);
        if ($turnCount > 0 && $playerCount > 0) {
            $nextPlayerIndex = $turnCount % $playerCount;
            $nextPlayer = $this->players[$nextPlayerIndex];
            return $nextPlayer;
        } else {
            $player = new stdClass();
            $player->id = null;
            return $player;
        }
    }

    function addPlayer($data) {
        if (!property_exists($data, "name")) { return false; }
        if (!property_exists($data, "id")) { return false; }

        // TODO: Check if the player already exists and if maxplayers is not reached yet

        $player = new Player($data->name, $data->id);
        $this->players[] = $player;
        $this->update();
        return $player;
    }

    // --- turn functions -------------------------------------------------------------------------
    function getTurns() {
        return $this->turns;
    }

    function addTurn($turn) {
        // check if the winning condition hasn't been met yet
        $gameWon = $this->parser->winningConditionFulfilled($this);
        if ($gameWon) {
            echo "Game already over.";
            http_response_code(400);
            return;
        }

        // Check if the right player did the turn
        if (property_exists($turn, "player")) {
            if ($turn && $turn->player == $this->nextPlayer()->id) {
                $turn = $this->parser->checkTurn($this, $turn);
                if ($turn) {
                    $this->turns[] = $turn;
                    $this->update();
                    echo "Turn added";
                    http_response_code(200);
                    return;
                } else {
                    echo "Invalid turn.";
                    http_response_code(400);
                    return;
                }
            } else {
                echo "Invalid player.";
                http_response_code(400);
                return;
            }
        } else {
            echo "Invalid turn.";
            http_response_code(400);
            return;
        }
    }


    // --- game functions -------------------------------------------------------------------------
    function update() {
        $data = array(
            "game" => $this->game,
            "lastModified" => time(),
            "players" => $this->players,
            "seed" => $this->seed,
            "turns" => $this->turns,
        );
        $data = json_encode($data);
        if (!is_dir("games/".$this->game)) {
            mkdir("games/".$this->game);
        }
        file_put_contents("games/".$this->game."/".$this->gameId.".json", $data);
    }

    function summary() {
        if (!$this->loaded) {
            return array(
                "error" => "Game not loaded"
            );
        }

        $summary = array(
            "game" => $this->game,
            "lastModified" => $this->lastModified ?: 0,
            "players" => $this->players,
            "seed" => $this->seed,
            "turns" => count($this->turns),
            "nextPlayer" => $this->nextPlayer()->id,
            "won" => $this->parser->winningConditionFulfilled($this),
        );
        return $summary;
    }


    // --- helper functions -----------------------------------------------------------------------
    function lastModifiedHeader() {
        return gmdate("D, d M Y H:i:s T", $this->lastModified);
    }
}


// --- interface for game parsers -----------------------------------------------------------------
interface iGame {
    public function parseTurns($game, $turns);
    public function checkTurn($game, $turn);
    public function winningConditionFulfilled($game);
}

class EmptyGame implements iGame {
    public function parseTurns($game, $turns) {}
    public function checkTurn($game, $turn) { return $turn; }
    public function winningConditionFulfilled($game) { return false; }
}

?>