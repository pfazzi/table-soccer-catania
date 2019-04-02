<?php

use Chovanec\Rating\Rating;

$results = [
    ['Umberto', 'Vittorio', 'Stefano', 'Dario', 12, 10],
    ['Patrick', 'Carmelo', 'Domenico', 'Mirko', 10, 5],
    ['Stefano', 'Dario', 'Domenico', 'Carmelo', 6, 10],
    ['Umberto', 'Dario', 'Patrick', 'Carmelo', 4, 10],
    ['Patrick', 'Carmelo', 'Domenico', 'Vittorio', 7, 10],
    ['Patrick', 'Carmelo', 'Domenico', 'Vittorio', 5, 10],
    ['Patrick', 'Carmelo', 'Vittorio', 'Dario', 10, 7],
    ['Patrick', 'Carmelo', 'Domenico', 'Dario', 10, 6],
    ['Stefano', 'Vittorio', 'Patrick', 'Carmelo', 12, 10],
    ['Stefano', 'Vittorio', 'Domenico', 'Carmelo', 10, 4],
    ['Patrick', 'Vittorio', 'Stefano', 'Dario', 8, 10],
    ['Patrick', 'Vittorio', 'Domenico', 'Carmelo', 7, 10],
    ['Domenico', 'Mirko', 'Patrick', 'Carmelo', 10, 5],
    ['Stefano', 'Vittorio', 'Patrick', 'Carmelo', 10, 5],
    ['Domenico', 'Vittorio', 'Stefano', 'Dario', 10, 5],
    ['Domenico', 'Carmelo', 'Patrick', 'Dario', 10, 4],
    ['Domenico', 'Mirko', 'Stefano', 'Carmelo', 4, 10],
    ['Patrick', 'Dario', 'Mirko', 'Vittorio', 7, 10],
    ['Stefano', 'Dario', 'Patrick', 'Peppe', 10, 4],
    ['Domenico', 'Vittorio', 'Mirko', 'Carmelo', 7, 10],
    ['Mirko', 'Dario', 'Stefano', 'Peppe', 11, 13],
    ['Domenico', 'Vittorio', 'Stefano', 'Carmelo', 12, 10],
    ['Peppe', 'Carmelo', 'Mirko', 'Dario', 10, 8],
    ['Peppe', 'Stefano', 'Dario', 'Vittorio', 12, 14],
    ['Domenico', 'Carmelo', 'Vittorio', 'Dario', 10, 4],

    ['Domenico', 'Carmelo', 'Patrick', 'Dario', 10, 6],
    ['Mirko', 'Vittorio', 'Stefano', 'Dario', 10, 5],
    ['Domenico', 'Vittorio', 'Luca', 'Patrick', 10, 7],
    ['Stefano', 'Vittorio', 'Luca', 'Carmelo', 13, 11],
];

$ranking = [];

$loader = require __DIR__ . '/vendor/autoload.php';

foreach ($results as $result) {
    [$team_A_palyer_A, $team_A_palyer_B, $team_B_palyer_A, $team_B_palyer_B, $score_team_A, $score_team_B] = $result;
    $ranking = update_ranking($ranking, $team_A_palyer_A, $team_A_palyer_B, $team_B_palyer_A, $team_B_palyer_B,
        $score_team_A, $score_team_B);
}

usort($ranking, function ($a, $b) {
    if ($a['rating'] == $b['rating']) {
        return 0;
    }
    return ($a['rating'] < $b['rating']) ? 1 : -1;
});

if (isset($_GET['ranking'])) {
    ranking_action($ranking);
} elseif (isset($_GET['games'])) {
    results_action($results);
} else {
    text_format_ranking($ranking);
}

function results_action(array $results)
{
    header("Access-Control-Allow-Origin: *");
    header('content-type application/json');

    echo json_encode([
        "games" => array_map(function ($result) {
            return [
                "DefenderA" => $result[0],
                "StrikerA" => $result[1],
                "DefenderB" => $result[2],
                "StrikerB" => $result[3],
                "ResultA" => $result[4],
                "ResultB" => $result[5],
            ];
        }, $results)
    ]);
}

function ranking_action(array $ranking)
{
    header("Access-Control-Allow-Origin: *");
    header('content-type application/json');

    $index = 1;
    echo json_encode([
        "ranking" => array_map(function ($player) use (&$index) {
            return [
                "position" => $index++,
                "name" => $player['name'],
                "points" => $player['rating']
            ];
        }, $ranking)
    ]);
}

function text_format_ranking(array $ranking)
{
    $index = 1;
    echo '<pre>';
    foreach ($ranking as $rating) {
        echo str_pad($index++ . ') ' . $rating['name'], 15) . $rating['rating'] . PHP_EOL;
    }
    echo '</pre>';
}

function update_ranking(
    $ranking,
    $team_A_palyer_A,
    $team_A_palyer_B,
    $team_B_palyer_A,
    $team_B_palyer_B,
    $score_team_A,
    $score_team_B
): array {
    $ranking = array_merge([
        $team_A_palyer_A => ['name' => $team_A_palyer_A, 'rating' => 1000, 'streak' => 0],
        $team_A_palyer_B => ['name' => $team_A_palyer_B, 'rating' => 1000, 'streak' => 0],
        $team_B_palyer_A => ['name' => $team_B_palyer_A, 'rating' => 1000, 'streak' => 0],
        $team_B_palyer_B => ['name' => $team_B_palyer_B, 'rating' => 1000, 'streak' => 0],
    ], $ranking);

    $team_A_ranking = $ranking[$team_A_palyer_A]['rating'] + $ranking[$team_A_palyer_B]['rating'];
    $team_B_ranking = $ranking[$team_B_palyer_A]['rating'] + $ranking[$team_B_palyer_B]['rating'];

    $score_team_A = $score_team_A > $score_team_B ? Rating::WIN : Rating::LOST;
    $score_team_B = $score_team_A ===  Rating::WIN ? Rating::LOST : Rating::WIN;

    $ratings = new Rating($team_A_ranking, $team_B_ranking, $score_team_A, $score_team_B);

    ['a' => $team_A_new_ranking, 'b' => $team_B_new_ranking] = $ratings->getNewRatings();

    $delta_A = (int) $team_A_new_ranking - $team_A_ranking;
    $delta_B = (int) $team_B_new_ranking - $team_B_ranking;

    $ranking[$team_A_palyer_A]['rating'] += $delta_A;
    $ranking[$team_A_palyer_B]['rating'] += $delta_A;

    $ranking[$team_B_palyer_A]['rating'] += $delta_B;
    $ranking[$team_B_palyer_B]['rating'] += $delta_B;

    return $ranking;
}

