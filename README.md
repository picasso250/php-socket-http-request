php-socket-http-request
=======================

获取一半的页面。

很多时候，没必要 Curl 完整的页面。但用 php-curl 你是不能控制这个的。

用这个就可以了。

```php
<?php

$fetch = new http\request\Fetch;
// 当获取完head之后，就不再获取了。
$html = $fetch->doGet('www.test.org', '/', function($_, $html) {
    return preg_match('%<head.+?</head>%is', $html);
});
var_dump($html);
```
