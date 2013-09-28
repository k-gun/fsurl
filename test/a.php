<?php
header('Content-Type: text/plain; charset=utf-8');

require_once './../FSUrl/FSUrl.php';
require_once './../FSUrl/FSUrlException.php';

$url = 'http://obushu.com/login';
$fsUrl = new FSUrl($url);
$fsUrl->setMethod(FSUrl::METHOD_POST);
$fsUrl->setRequestBody(array(
    'username' => 'foo',
    'password' => '****',
));
$fsUrl->run();

// pre($fsUrl,1);
pre($fsUrl->getRequest());
pre($fsUrl->getResponseHeaders(true));

pre("\n\n\n");

$cookies = $fsUrl->getCookies();

$url = 'http://obushu.com/';
$fsUrl = new FSUrl($url);
$fsUrl->setRequestHeader('Cookie', $cookies);
$fsUrl->run();

pre($fsUrl->getRequest());
pre($fsUrl->getResponseHeaders(true));

// pre($fsUrl->getStatusCode());
// pre($fsUrl->getStatusText());

die;

/*
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
    printf("%s\n", print_r($s, 1));
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

simultaneously -> http://www.php.net/manual/en/function.fsockopen.php#33888

Ilerde FSUrlRequest & FSUrlResponse diye ayir bunun icindekileri
$responseHeaders      = $fs->response->getHeaders();
$responseHeaderCookie = $fs->response->getHeader('Cookie');
$responseStatusCode   = $fs->response->getStatuscode();
...
*/