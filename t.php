<?php

require __DIR__.'/http/request/Fetch.php';

$fetch = new http\request\Fetch;
$a = $fetch->doGet('www.yunday.org');
var_dump($a);
