<?php

require __DIR__.'/http/request/Fetch.php';

$fetch = new http\request\Fetch;
$a = $fetch->doGet('www.test.org');
var_dump($a);


$fetch = new http\request\Fetch;
$html = $fetch->doGet('www.test.org', '/', function($_, $html) {
    return preg_match('%<head.+?</head>%is', $html);
});
var_dump($html);
