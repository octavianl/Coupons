<?php

/**
 * Http Post
 *
 * @category Http post
 * @package  OAUTH2
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */

class HttpPost
{
    public $url;
    public $postString;
    public $httpResponse;
    public $ch;
    public $headers;
/**
* Constructs an HttpPost object and initializes CURL
*
* @param url the url to be accessed
*/
    public function __construct($url, $headers)
    {
        $this->url = $url;
        $this->headers = $headers;
        $this->ch = curl_init($this->url);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, false);
        //curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        //curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
    }
/**
* shut down CURL before destroying the HttpPost object
*/
    public function __destruct()
    {
        curl_close($this->ch);
    }
/**
* Convert an incoming associative array into a POST string
* for use with our HTTP POST
*
* @param params an associative array of data pairs
*/
    public function setPostData($params)
    {
        // http_build_query encodes URLs, which breaks POST data
        $this->postString = rawurldecode(http_build_query($params));
        curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->postString);
    }
    
    public function setGetData($params)
    {
        curl_setopt($this->ch, CURLOPT_HTTPGET, true);
    }        
/**
* Make the POST request to the server
*/
    public function send()
    {
        $this->httpResponse = curl_exec($this->ch);
        
        if (curl_errno($this->ch)) {
            print "Error: " . curl_error($this->ch);
        }
    }
/**
* Read the HTTP Response returned by the server
*/
    public function getResponse()
    {
        return $this->httpResponse;
    }
}
