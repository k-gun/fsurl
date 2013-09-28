<?php
/**
 * Copyright 2013, Kerem Gunes <http://qeremy.com/>.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

/**
 * @class FSUrl v0.2
 *
 * FSUrl object.
 */
class FSUrl
{
    // Version
    protected static $_version = '0.2';

    // Request methods
    const
        METHOD_GET    = 'GET',
        METHOD_POST   = 'POST',
        METHOD_PUT    = 'PUT',
        METHOD_DELETE = 'DELETE',
        METHOD_HEAD   = 'HEAD';

    // FSUrl handler
    protected $_fp = null;
    // Target URL
    protected $_url = array();
    // FSUrl method
    protected $_method = self::METHOD_GET;
    // FSUrl options
    protected $_options = array(
        'timeout' => 5,
        'blocking' => true,
        'http_version' => '1.1',
    );

    // Request stuff
    protected $_request = null,
              $_requestBody = '',
              $_requestHeaders = array(),
              $_requestHeadersRaw = '';
    // Response stuff
    protected $_response = null,
              $_responseBody = '',
              $_responseHeaders = array(),
              $_responseHeadersRaw = '';

    // Store reponse headers & body?
    protected $_storeResponseHeaders = true,
              $_storeResponseBody = true;

    // FSUrl errno & errstr
    protected $_failCode = 0,
              $_failText = '';

    // Schemes & Ports maps
    protected $_sp = array(
        'http'  => array('protocol' => 'tcp://', 'port' => 80),
        'https' => array('protocol' => 'ssl://', 'port' => 443),
        // 'udp' => ... migth be extended
    );

    // Cookie store
    protected $_cookies = null;

    /**
     * Make a new FSUrl instance (with the given arguments).
     *
     * @param string $url
     * @param array  $options
     * @throws FSUrlException
     */
    public function __construct($url, Array $options = array()) {
        // Parse URL
        extract(parse_url($url));

        // Check URL host
        if (!isset($host)) {
            throw new FSUrlException('Host required!');
        }
        $fsHost = $host;

        // Set scheme
        isset($scheme) or $scheme = 'http';
        // Set port & fs host
        if (isset($this->_sp[$scheme])) {
            $port = $this->_sp[$scheme]['port'];
            $fsHost = $this->_sp[$scheme]['protocol'] . $host;
        }
        // Set path
        isset($path) or $path = '/';
        // Set query
        $query = isset($query) ? '?'. $query : '';

        $this->_url = compact(array(
            'fsHost', 'scheme', 'host', 'port', 'path', 'query'
        ));

        // Initial request headers
        $requestHeaders = array(
            'User-Agent' => isset($this->_requestHeaders['User-Agent'])
                ? $this->_requestHeaders['User-Agent']
                : 'FSUrl/v'. self::$_version .' (+http://github.com/qeremy/fsurl)',
            'Host'       => $this->_url['host'],
            'Accept'     => '*/*',
            'Connection' => 'close', // Important for a quick connection!!!
        );
        $this->setRequestHeader($requestHeaders);
    }

    /**
     * Execute FSUrl.
     */
    public function run() {
        // Get request & request headers ready
        $this->_prepareRequest();

        // Open a socket connection
        $this->_fp =@ fsockopen(
            $this->_url['fsHost'],
            $this->_url['port'],
            $this->_failCode,
            $this->_failText,
            $this->getOption('timeout')
        );

        if ($this->_fp) {
            // Write request headers & body
            fwrite($this->_fp, $this->_request);

            stream_set_blocking($this->_fp, $this->getOption('blocking'));
            stream_set_timeout($this->_fp, $this->getOption('timeout'));
            $meta = stream_get_meta_data($this->_fp);

            // Get response
            while (!feof($this->_fp)) {
                if ($meta['timed_out']) {
                    throw new FSUrlException('Time out!');
                }
                $this->_response .= fgets($this->_fp, 1024);
                $meta = stream_get_meta_data($this->_fp);
            }

            // Destroy pointer
            fclose($this->_fp); $this->_fp = null;

            // Parse response & set headers and body
            @ list($headers, $body) = explode("\r\n\r\n", $this->_response, 2);
            $this->_setResponseHeaders($headers);
            $this->_setResponseBody($body);
        }
    }

    /**
     * Set request method
     *
     * @param string $method
     */
    public function setMethod($method) {
        $this->_method = strtoupper($method);
    }

    /**
     * Set URL scheme
     *
     * @param string $scheme
     */
    public function setScheme($scheme) {
        $this->_url['scheme'] = strtolower($scheme);
    }

    /**
     * Set URL host
     *
     * @param string $host
     */
    public function setHost($host) {
        $this->_url['host'] = $host;
    }

    /**
     * Set URL port
     *
     * @param int $port
     */
    public function setPort($port) {
        $this->_url['port'] = (int) $port;
    }

    /**
     * Set option
     *
     * @param string $key
     * @param mixed $val
     */
    public function setOption($key, $val) {
        $this->_options[$key] = $val;
    }

    /**
     * Get request method
     */
    public function getMethod() {
        return $this->_method;
    }

    /**
     * Get URL scheme
     */
    public function getScheme() {
        return $this->_url['scheme'];
    }

    /**
     * Get URL host
     */
    public function getHost() {
        return $this->_url['host'];
    }

    /**
     * Get URL port
     */
    public function getPort() {
        return $this->_url['port'];
    }

    /**
     * Get option
     *
     * @param string $key
     */
    public function getOption($key) {
        if (isset($this->_options[$key])) {
            return $this->_options[$key];
        }
    }

    /**
     * Get URL (or URL variable)
     *
     * @param string $key
     */
    public function getUrl($key = null) {
        if ($key === null) {
            return $this->_url;
        }
        return isset($this->_url[$key]) ? $this->_url[$key] : null;
    }

    /**
     * Set request body
     *
     * @param string $body
     */
    public function setRequestBody($body) {
        // Do not append if request method is GET
        if ($this->_method == self::METHOD_GET) {
            return;
        }
        // Convert body to query string
        if (is_array($body)) {
            $body = http_build_query($body);
        }

        $this->_requestBody = (string) $body;
        // Add required headers
        $this->setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        $this->setRequestHeader('Content-Length', strlen($this->_requestBody));
    }

    /**
     * Set request header(s).
     *
     * @param mixed $key
     * @param mixed $val
     */
    public function setRequestHeader($key, $val = null) {
        // X-Foo => The Foo!
        if (is_array($key) && !empty($key)) {
            foreach ($key as $k => $v) {
                if (is_int($k)) {
                    $this->setRequestHeader($v);
                } else {
                    $this->setRequestHeader($k, $v);
                }
            }
        }
        // X-Foo: The Foo!
        if ($val === null) {
            @list($key, $val) = explode(':', $key, 2);
        }

        if ($key) {
            $this->_requestHeaders[$key] = trim($val);
        }

        return $this;
    }

    /**
     * Get raw request.
     */
    public function getRequest() {
        return $this->_request;
    }

    /**
     * Get request body.
     */
    public function getRequestBody() {
        return $this->_requestBody;
    }

    /**
     * Get request header.
     *
     * @param string $key
     * @param mixed $prepareKey
     */
    public function getRequestHeader($key, $prepareKey = false) {
        if ($prepareKey) {
            $key = preg_replace_callback('~_([a-z])~i', function($m){
                return '-'. strtoupper($m[1]);
            }, ucfirst($key));
        }
        return isset($this->_requestHeaders[$key])
            ? $this->_requestHeaders[$key] : null;
    }

    /**
     * Get request headers (raw).
     *
     * @param bool $raw
     */
    public function getRequestHeaders($raw = false) {
        return !$raw
            ? $this->_requestHeaders
            : $this->_requestHeadersRaw;
    }

    /**
     * Get raw response.
     */
    public function getResponse() {
        return $this->_response;
    }

    /**
     * Get response body.
     */
    public function getResponseBody() {
        return $this->_responseBody;
    }

    /**
     * Get response header.
     *
     * @param string $key
     * @param mixed $prepareKey
     */
    public function getResponseHeader($key, $prepareKey = true) {
        if ($prepareKey && ($key != 'status_code' && $key != 'status_text')) {
            $key = preg_replace_callback('~_([a-z])~i', function($m){
                return '-'. strtoupper($m[1]);
            }, ucfirst($key));
        }
        return isset($this->_responseHeaders[$key])
            ? $this->_responseHeaders[$key] : null;
    }

    /**
     * Get response headers (raw).
     *
     * @param bool $raw
     */
    public function getResponseHeaders($raw = false) {
        return !$raw
            ? $this->_responseHeaders
            : $this->_responseHeadersRaw;
    }

    /**
     * Get response cookies ready to send later
     *
     * @return string
     */
    public function getCookies() {
        if (empty($this->_cookies) && isset($this->_responseHeaders['Set-Cookie'])) {
            $cookies = array();
            foreach ((array) $this->_responseHeaders['Set-Cookie'] as $cookie) {
                $cookies[] = preg_replace('~^([^;]+).*~', '\\1', trim($cookie));
            }
            $this->_cookies = join('; ', $cookies);
        }
        return $this->_cookies;
    }

    /**
     * Get response status code.
     */
    public function getStatusCode() {
        return isset($this->_responseHeaders['status_code'])
            ? $this->_responseHeaders['status_code'] : 0;
    }

    /**
     * Get response status text.
     */
    public function getStatusText() {
        return isset($this->_responseHeaders['status_text'])
            ? $this->_responseHeaders['status_text'] : '';
    }

    /**
     * Check FSUrl error.
     */
    public function isFail() {
        return (bool) ($this->_failText !== '');
    }

    /**
     * Get FSUrl error code.
     */
    public function getFailCode() {
        return $this->_failCode;
    }

    /**
     * Get FSUrl error text.
     */
    public function getFailText() {
        return $this->_failText;
    }

    /**
     * Prepare request headers and body (append body to request).
     */
    protected function _prepareRequest() {
        $this->_prepareRequestHeaders();
        $this->_prepareRequestBody();
        $this->_request = $this->_requestHeadersRaw ."\r\n";
        if (strlen($this->_requestBody)) {
            $this->_request .= $this->_requestBody;
        }
    }

    /**
     * Prepare request headers.
     */
    protected function _prepareRequestHeaders() {
        $this->_requestHeadersRaw = sprintf("%s %s HTTP/%s\r\n",
            $this->_method, $this->_url['path'] . $this->_url['query'], $this->_options['http_version']);

        foreach ($this->_requestHeaders as $key => $val) {
            $this->_requestHeadersRaw .= "$key: $val\r\n";
        }
    }

    /**
     * Prepare request body.
     */
    protected function _prepareRequestBody() {
        if (is_array($this->_requestBody)) {
            $this->_requestBody = http_build_query($this->_requestBody);
        }
    }

    /**
     * Set reponse body.
     */
    protected function _setResponseBody($body) {
        if ($this->_storeResponseBody && !$this->getOption('nobody')) {
            $this->_responseBody = trim($body);
        }
    }

    /**
     * Set reponse headers.
     */
    protected function _setResponseHeaders($headers) {
        if ($this->_storeResponseHeaders && !$this->getOption('noheaders')) {
            $this->_responseHeaders   += $this->_parseHeaders($headers);
            $this->_responseHeadersRaw = trim($headers);
        }
    }

    /**
     * Parse headers.
     *
     * @param mixed $headers
     */
    protected function _parseHeaders($headers) {
        $headersArr = array();
        $headersTmp = $headers;
        if (is_string($headersTmp)) {
            $headersTmp =@ explode("\r\n", $headers);
        }

        if (is_array($headersTmp) || !empty($headersTmp)) {
            foreach ($headersTmp as $header) {
                // E.g: HTTP/1.1 301 Moved Permanently
                if (preg_match('~^HTTP/[\d\.]+ (\d+) ([\w- ]+)~i', $header, $matches)) {
                    $headersArr['status_code'] = (int) $matches[1];
                    $headersArr['status_text'] = trim($matches[2]);
                    continue;
                }
                @ list($key, $val) = explode(':', $header, 2);
                if ($key) {
                    $val = trim($val);
                    // Handle multi-headers as array
                    if (array_key_exists($key, $headersArr)) {
                        $headersArr[$key] = array_merge((array) $headersArr[$key], array($val));
                        continue;
                    }
                    $headersArr[$key] = $val;
                }
            }
            ksort($headersArr);
        }

        return $headersArr;
    }
}