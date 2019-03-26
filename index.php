<?php
declare(strict_types=1);


use Chovanec\Rating\Rating;

$loader = require __DIR__ . '/vendor/autoload.php';

$rating = new Rating(1000, 2000, Rating::LOST, Rating::WIN);

print_r($rating->getNewRatings());

