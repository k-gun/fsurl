**Usage**

- Simple

`$url = http://uri.li/cJjN`

```php
$fsUrl = new FSUrl($url);

// Execute request
$fsUrl->run();

print_r($fsUrl->getRequestHeaders());
print_r($fsUrl->getResponseHeaders());

/* Result
Array
(
    ...
    [host] => uri.li
    [user_agent] => FSUrl/v1.0
)

Array
(
    ...
    [content_length] => 0
    [content_type] => text/html
    [location] => http://google.com/
    [pragma] => no-cache
    [response_code] => 301
    [response_text] => Moved Permanently
    [set_cookie] => Array
        (
            [0] => ...
            [1] => ...
        )
    [vary] => Accept-Encoding
)
*/

// Work with response headers
$responseHeaders = $fsUrl->getResponseHeaders();
if ($responseHeaders['response_code'] >= 400) {
    printf('Error: %s', $responseHeaders['response_text']);
}

// Work with response body
$responseBody = $fsUrl->getResponseBody();
$dom = new Dom($responseBody); // trivial class just for example
print $dom->getElementById('foo')->getAtrribute('src');
```

- Set & get options

```php
$fsUrl->setOption('timeout', 10);

print $fsUrl->getOption('timeout');
```

- Set & get method

```php
$fsUrl->setMethod(FSUrl::METHOD_POST);

print $fsUrl->getMethod() // POST
```

- Request

```php
// set headers
$fsUrl->setRequestHeader('X-Foo-1: foo1');
$fsUrl->setRequestHeader(array('X-Foo-2: foo2'));
$fsUrl->setRequestHeader(array('X-Foo-3' => 'foo3'));

// set body (while posting data)
// Note: Doesn't work if FSUrl method is GET
$fsUrl->setRequestBody('foo=1&bar=The+bar%21');
$fsUrl->setRequestBody(array(
    'foo' => 1,
    'bar' => 'The bar!'
));

// get raw equest
print $fsUrl->getRequest();
/*
GET /cJjN HTTP/1.1
User-Agent: FSUrl/v1.0
Host: uri.li
...
*/

// get request body
$fsUrl->getRequestBody();

// get request header
$fsUrl->getRequestHeader('host');
// get request headers
$fsUrl->getRequestHeaders(); // array(...)
// get request headers raw?
$fsUrl->getRequestHeaders(true);
```

- Response

```php
// get raw response
$fsUrl->getResponse();
/*
HTTP/1.1 301 Moved Permanently
Server: nginx
Date: Thu, 22 Aug 2013 22:34:22 GMT
Content-Type: text/html
Content-Length: 0
...
*/

// get response body
$fsUrl->getResponseBody();

// get response header
$fsUrl->getResponseHeader('response_code');
// get response headers
$fsUrl->getResponseHeaders(); // array(...)
// get response headers raw?
$fsUrl->getResponseHeaders(true);

// not storing response headers & body
$fsUrl->storeResponseHeaders(false);
$fsUrl->storeResponseBody(false);
```

- Error handling

```php
if ($fsUrl->isFail()) {
    printf('Error! Code[%d] Text[%s]',
        $fsUrl->getFailCode(), $fsUrl->getFailText());
}
```