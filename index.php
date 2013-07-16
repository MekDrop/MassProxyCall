<?php

if (!isset($_REQUEST['url']))
    die("Missing REUEST[url]");

header('Content-Type: text/plain');
header('Connection: Keep-Alive');

@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);

$proxies = file(__DIR__ . '/proxies.php');

echo "Loaded " . count($proxies) . ' proxies' . "\r\n";
@ob_flush();
@flush();

if (!is_array($_REQUEST['url']))
    $_REQUEST['url'] = array($_REQUEST['url']);

$m = 1;
$c = count($_REQUEST['url']);
foreach ($_REQUEST['url'] as $request_url) {
    
    echo "Processing $request_url ($m of $c)...\r\n";
    @ob_flush();
    @flush();
    
    $i = 0;
    foreach ($proxies as $url) {
        $url = trim($url);
        $proxy = parse_url($url);

        echo str_pad(($i++)+1, 7, ' ', STR_PAD_LEFT) . ". Fetching from proxy $url...\r\n";    
        @ob_flush();
        @flush();

        $time = microtime(true);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$request_url);
        curl_setopt($ch, CURLOPT_PROXY, $proxy['host'] . ':' . $proxy['port']);
        //curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $curl_scraped_page = curl_exec($ch);
        curl_close($ch);

        echo str_repeat(' ', 10) . "Got result of size " . strlen($curl_scraped_page) .".\r\n";
        echo str_repeat(' ', 10) . "Took " . abs(microtime(true) - $time) . "s\r\n"; 
        @ob_flush();
        @flush();

    }
    
    $m++;
}