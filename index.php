<?php

use Chovanec\Rating\Rating;

$results = [
    ['Umberto', 'Vittorio', 'Stefano', 'Dario', '12', '10'],
    ['Patrick', 'Carmelo', 'Domenico', 'Mirko', '10', '5'],
    ['Stefano', 'Dario', 'Domenico', 'Carmelo', '6', '10'],
    ['Umberto', 'Dario', 'Patrick', 'Carmelo', '4', '10'],
    ['Patrick', 'Carmelo', 'Domenico', 'Vittorio', '7', '10'],
    ['Patrick', 'Carmelo', 'Domenico', 'Vittorio', '5', '10'],
    ['Patrick', 'Carmelo ', 'Vittorio', 'Dario', '10', '7'],
    ['Patrick', 'Carmelo', 'Domenico', 'Dario', '10', '6'],
    ['Stefano', 'Vittorio', 'Patrick', 'Carmelo', 'AB', '10'],
    ['Stefano', 'Vittorio', 'Domenico', 'Carmelo', '10', '4'],
    ['Patrick', 'Vittorio', 'Stefano', 'Dario', '8', '10'],
    ['Patrick', 'Vittorio', 'Domenico', 'Carmelo', '7', '10'],
    ['Domenico', 'Mirko', 'Patrick', 'Carmelo', '10', '5'],
    ['Stefano', 'Vittorio', 'Patrick', 'Carmelo', '10', '5']
];

$ranking = [];

$loader = require __DIR__ . '/vendor/autoload.php';

foreach ($results as $result) {
    [$team_A_palyer_A,  $team_A_palyer_B, $team_B_palyer_A, $team_B_palyer_B, $score_team_A, $score_team_B] = $result;
    $ranking = update_ranking($ranking, $team_A_palyer_A,  $team_A_palyer_B, $team_B_palyer_A, $team_B_palyer_B, $score_team_A, $score_team_B);
}

print_r($ranking);

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
        $team_A_palyer_A => 1000,
        $team_A_palyer_B => 1000,
        $team_B_palyer_A => 1000,
        $team_B_palyer_B => 1000,
    ], $ranking);

    $team_A_ranking = $ranking[$team_A_palyer_A] + $ranking[$team_A_palyer_B];
    $team_B_ranking = $ranking[$team_B_palyer_A] + $ranking[$team_B_palyer_B];

    $score_team_A = $score_team_A > $score_team_B ? Rating::WIN : Rating::LOST;
    $score_team_B = $score_team_A ===  Rating::WIN ? Rating::LOST : Rating::WIN;

    $ratings = new Rating($team_A_ranking, $team_B_ranking, $score_team_A, $score_team_B);

    ['a' => $team_A_new_ranking, 'b' => $team_B_new_ranking] = $ratings->getNewRatings();

    $delta_A = (int) $team_A_new_ranking - $team_A_ranking;
    $delta_B = (int) $team_B_new_ranking - $team_B_ranking;

    $ranking[$team_A_palyer_A] += $delta_A;
    $ranking[$team_A_palyer_B] += $delta_A;

    $ranking[$team_B_palyer_A] += $delta_B;
    $ranking[$team_B_palyer_B] += $delta_B;

    return $ranking;
}

