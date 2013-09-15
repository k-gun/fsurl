<?php
require_once './../FSUrl/FSUrlException.php';
require_once './../FSUrl/FSUrl.php';

$url = 'http://uri.li/cJjN';
$fsUrl = new FSUrl($url);
$fsUrl->setMethod(FSUrl::METHOD_POST);
$fsUrl->run();

pre($fsUrl->getRequest());
pre($fsUrl->getResponse());

die;

// $url = 'http://www.facebook.com/';
// $rdr = [$url];
// do {
//     $retry = false;
//     $fsUrl = new FSUrl($url);
//     $fsUrl->run();
//     if ($fsUrl->getResponseHeader('response_code') != 200
//             && ($url = $fsUrl->getResponseHeader('location'))) {
//         $retry = true;
//         $rdr[] = $url;
//     }
// } while ($retry && count($rdr) < 3);

// pre($rdr);

// $cookie ???


/*
$fp = fsockopen("dev.local", 80, $errno, $errstr, 30);
if (!$fp) {
    echo "$errstr ($errno)<br />\n";
} else {
    $out = "GET / HTTP/1.1\r\n";
    $out .= "Host: dev.local\r\n";
    $out .= "Connection: Close\r\n\r\n";
    fwrite($fp, $out);
    while (!feof($fp)) {
        echo fgets($fp, 128);
    }
    fclose($fp);
}

$fp = fsockopen('www.google.com', 80, $errno, $errstr, 5);

$input  = "GET /user/123 HTTP/1.1\r\n";
$input .= "Host: www.example.com\r\n";
$input .= "Connection: Close\r\n\r\n";
fwrite($fp, $input);

$result = '';
while (!feof($fp)) {
    $result .= fgets($fp, 128);
}
fclose($fp);

//////////////////////////////////////////////////////////

$fp = fsockopen('www.google.com', 80, $errno, $errstr, 5);
fputs($fp, "POST /user/123 HTTP/1.1\r\n");
fputs($fp, "Host: $host\r\n");
fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
fputs($fp, "Content-length: ". strlen($data) ."\r\n");
fputs($fp, "Connection: close\r\n\r\n");
fputs($fp, $data);

$result = '';
while(!feof($fp)) {
    $result .= fgets($fp, 128);
}
fclose($fp);
*/

function pre($s, $e = 0) {
    printf('<pre>%s</pre>', print_r($s, 1));
    if ($e) exit;
}

/*
$url = 'http://google.co';
$rdr = [$url];
do {
    $retry = false;
    $fsUrl = new FSUrl($url);
    $fsUrl->run();
    if ($fsUrl->getResponseHeader('response_code') != 200
            && ($url = $fsUrl->getResponseHeader('location'))) {
        $retry = true;
        $rdr[] = $url;
    }
} while ($retry);

print_r($rdr);

Array
(
    [0] => http://google.co
    [1] => http://www.google.com/
    [2] => http://www.google.com.tr/?gws_rd=cr&ei=tbYbUtDEKs3HsgaxwYGQAg
)
*/