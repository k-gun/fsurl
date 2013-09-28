**Usage**

- Simple

`$url = http://uri.li/cJjN`

```php
$fs = new FSUrl($url);

// Execute request
$fs->run();

print_r($fs->getRequestHeaders());
print_r($fs->getResponseHeaders());

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
    [status_code] => 301
    [status_text] => Moved Permanently
    [set_cookie] => Array
        (
            [0] => ...
            [1] => ...
        )
    [vary] => Accept-Encoding
)
*/

// Check response status
print $fs->getStatusCode(); // 301
print $fs->getStatusText(); // Move Permanently

// Work with response headers
$responseHeaders = $fs->getResponseHeaders();
if ($responseHeaders['status_code'] >= 400) {
    printf('Error: %s', $responseHeaders['status_text']);
}

// Work with response body
$responseBody = $fs->getResponseBody();
$dom = new Dom($responseBody); // trivial class just for example
print $dom->getElementById('foo')->getAtrribute('src');
```

- Set & get options

```php
$fs->setOption('timeout', 10);

print $fs->getOption('timeout');
```

- Set & get method

```php
$fs->setMethod(FSUrl::METHOD_POST);

print $fs->getMethod() // POST
```

- Request

```php
// set headers
$fs->setRequestHeader('X-Foo-1: foo1');
$fs->setRequestHeader(array('X-Foo-2: foo2'));
$fs->setRequestHeader(array('X-Foo-3' => 'foo3'));

// set body (while posting data)
// Note: Doesn't work if FSUrl method is GET
$fs->setRequestBody('foo=1&bar=The+bar%21');
$fs->setRequestBody(array(
    'foo' => 1,
    'bar' => 'The bar!'
));

// get raw equest
print $fs->getRequest();
/*
GET /cJjN HTTP/1.1
User-Agent: FSUrl/v1.0
Host: uri.li
...
*/

// get request body
$fs->getRequestBody();

// get request header
$fs->getRequestHeader('host');
// get request headers
$fs->getRequestHeaders(); // array(...)
// get request headers raw?
$fs->getRequestHeaders(true);
```

- Response

```php
// get raw response
$fs->getResponse();
/*
HTTP/1.1 301 Moved Permanently
Server: nginx
Date: Thu, 22 Aug 2013 22:34:22 GMT
Content-Type: text/html
Content-Length: 0
...
*/

// get response body
$fs->getResponseBody();

// get response header
$fs->getResponseHeader('status_code');
// get response headers
$fs->getResponseHeaders(); // array(...)
// get response headers raw?
$fs->getResponseHeaders(true);

// not storing response headers & body
$fs->storeResponseHeaders(false);
$fs->storeResponseBody(false);
```

- Cookies

```php
// Login first
$fs = new FSUrl('http://foo.com/login');
$fs->setMethod(FSUrl::METHOD_POST);
$fs->setRequestBody(array(
    'username' => 'foo',
    'password' => '****',
));
$fs->run();

// Store cookies
$cookies = $fs->getCookies();

// User profile page (that login requried)
$fs = new FSUrl('http://foo.com/profile');
$fs->setRequestHeader('Cookie', $cookies);
$fs->run();

print $fs->getStatusCode(); // 200 (login ok)
```

- Error handling

```php
if ($fs->isFail()) {
    printf('Error! Code[%d] Text[%s]',
        $fs->getFailCode(), $fs->getFailText());
}
```