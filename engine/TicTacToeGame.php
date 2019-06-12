<?php

class TicTacToeGame implements iGame {

    private $field;

    function parseTurns($game, $turns) {
        $this->field = array(
            array(NULL, NULL, NULL),
            array(NULL, NULL, NULL),
            array(NULL, NULL, NULL),
        );

        $players = $game->getPlayers();
        foreach ($turns as $turn) {
            $playerMark = $turn->player == $players[0]->id ? "x" : "o";
            $this->field[$turn->mark[0]][$turn->mark[1]] = $playerMark;
        }
    }

    function checkTurn($game, $turn) {
        // check if the turn has all the needed elements
        if (!property_exists($turn, "mark")) { return false; }
        if (!is_array($turn->mark)) { return false; }
        if (count($turn->mark) < 2) { return false; }

        // check if the turn satisfies the rules
        $mark = $turn->mark;

        $players = $game->getPlayers();
        $playerMark = $turn->player == $players[0]->id ? "x" : "o";

        $field = $this->field[$mark[0]][$mark[1]];
        if (is_null($field)) {
            $this->field[$mark[0]][$mark[1]] = $playerMark;
            return $turn;
        } else {
            return false;
        }
    }

    function winningConditionFulfilled($game) {
        return (
            // horizontal
            (!is_null($this->field[0][0]) && $this->field[0][0] == $this->field[0][1] && $this->field[0][1] == $this->field[0][2]) ||
            (!is_null($this->field[1][0]) && $this->field[1][0] == $this->field[1][1] && $this->field[1][1] == $this->field[1][2]) ||
            (!is_null($this->field[2][0]) && $this->field[2][0] == $this->field[2][1] && $this->field[2][1] == $this->field[2][2]) ||

            // vertical
            (!is_null($this->field[0][0]) && $this->field[0][0] == $this->field[1][0] && $this->field[1][0] == $this->field[2][0]) ||
            (!is_null($this->field[0][1]) && $this->field[0][1] == $this->field[1][1] && $this->field[1][1] == $this->field[2][1]) ||
            (!is_null($this->field[0][2]) && $this->field[0][2] == $this->field[1][2] && $this->field[1][2] == $this->field[2][2]) ||

            // diagonal
            (!is_null($this->field[0][0]) && $this->field[0][0] == $this->field[1][1] && $this->field[1][1] == $this->field[2][2]) ||
            (!is_null($this->field[0][2]) && $this->field[0][2] == $this->field[1][1] && $this->field[1][1] == $this->field[2][0])
        );
    }
}

?>