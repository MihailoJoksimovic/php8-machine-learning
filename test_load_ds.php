<?php

var_dump('Mem usage: ' . memory_get_usage() / 1024 / 1024 . " MB");

require_once './Dataset.php';
require_once './Formatter.php';

ini_set('memory_limit', '1G');

$formatter = new Formatter();

$movies = array_map('str_getcsv', file('./movies.csv'));
$ratings = array_map('str_getcsv', file('./ratings.csv'));

$movies = new Dataset($movies);
$ratings = new Dataset($ratings);

$mixaTest = new Dataset([
   ['d1', 'd2', 'd3'],
   [33, 33, 33],
   [33, 11, 19]
]);

var_dump($mixaTest->cosineSimilarityByNumericalIndex(0, 1)); die();

//var_dump($movies->columns());

//var_dump($movies->range(0, 10));

//var_dump($ratings->columns());
//var_dump($ratings->range(0, 1));

//$formatter->printData($movies, $movies->range(0, 10));
//$formatter->printData($ratings, $ratings->range(0, 10));

//
$pivoted = $ratings->pivot(index: 'userId', column: 'movieId', value: 'rating');

//var_dump($pivoted->range(0, 10)); die();

unset($ratings);

//var_dump($formatter->printData($pivoted, $pivoted->range(0, 10))); die();
var_dump('Mem usage: ' . memory_get_usage() / 1024 / 1024 . " MB");

//var_dump($pivoted->columns());die();

var_dump($pivoted->cosineSimilarity('1', '5'));
var_dump($pivoted->cosineSimilarity('1', '6'));
var_dump($pivoted->cosineSimilarity('1', '1')); die();


//var_dump('Mem usage: ' . memory_get_usage() / 1024 / 1024 . " MB");

//var_dump($movies); die();

//$data = fgetcsv(fopen('./movies.csv', 'r'));


//var_dump($movies[0]);
//var_dump($movies[1]);

//var_dump($csv); die();®®