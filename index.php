<?php

foreach (glob("engine/*.php") as $filename) {
    include $filename;
}

/*
function bc_hexdec($input) {
    $output = "0";

    if (preg_match('/^(0x)?(?P<hex>[a-f0-9]+)$/i', $input, $matches)) {
        foreach (str_split(strrev($matches['hex'])) as $index => $hex) {
            $output = bcadd($output, bcmul(strval(hexdec($hex)), bcpow('16', strval($index))));
        }
    }
    return $output;
}

$hash = "a94a8fe5ccb19ba61c4c0873d391e987982fbbd3";
$intHash = bc_hexdec($hash);

echo (int)$intHash;
*/

function error($statuscode=400, $errormessage) {
    http_response_code(400);
    header("Content-Type: application/json");
    $error = array(
        "error" => $errormessage
    );
    echo json_encode($error);
    die();
}

$params = explode("/", $_SERVER["REQUEST_URI"]);
@$gameType = $params[2] ?: "_";
@$gameId = $params[3] ?: uniqid();
@$action = $params[4] ?: "summary";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    switch ($action) {
        case "summary":
            $game = new Game($gameType, $gameId);

            if ($game) {
                $summary = $game->summary();
                header("Content-Type: application/json");
                header("Last-Modified: ".$game->lastModifiedHeader());
                echo json_encode($summary);
            } else {
                error(400, "Error loading game #".$gameId);
            }
            break;
        case "turns":
            $game = new Game($gameType, $gameId);
            $turns = $game->getTurns();
            header("Content-Type: application/json");
            header("Last-Modified: ".$game->lastModifiedHeader());
            echo json_encode($turns);
            break;
        default:
            // for development purposes, to be removed later
            var_dump($params);
            break;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $body = file_get_contents('php://input');

    switch ($action) {
        case "create":
            $gameSeed = uniqid();
            $game = new Game($gameType, null, $gameSeed);
            $game->update();

            $summary = $game->summary();
            header("Content-Type: application/json");
            header("Last-Modified: ".$game->lastModifiedHeader());
            echo json_encode($summary);
            break;

        case "addplayer":
            $game = new Game($gameType, $gameId);
            $player = json_decode($body);
            if (json_last_error() === 0) {
                $game->addPlayer($player);
            } else {
                error(400, "Invalid JSON: ".$body);
            }
            break;

        case "turn":
            $game = new Game($gameType, $gameId);
            $turn = json_decode($body);
            if (json_last_error() === 0) {
                $game->addTurn($turn);
            } else {
                error(400, "Invalid JSON: ".$body);
            }
            break;
        }
}

?>