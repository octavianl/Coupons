<?php

/**
 * CURL Api
 *
 * @category CURL
 * @package  OAUTH2
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */

class CurlApi
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
    public function __construct($url)
    {
        $this->url = $url;
        $this->ch = curl_init($this->url);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 60);        
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($this->ch, CURLOPT_VERBOSE, true);
    }
    /**
     * Shut down CURL before destroying the HttpPost object
    */
    public function __destruct()
    {
        curl_close($this->ch);
    }
    
    /**
     * Set the headers
     * 
     * @param string $headers The necessary headers for the current request
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
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
    
    /**
     * Set options for get data
     * 
     * @return void
     */
    public function setGetData()
    {
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLOPT_HTTPGET, true);
    }
    
    /**
    * Make the request to the server
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
    * 
    * @return string
    */
    public function getResponse()
    {
        return $this->httpResponse;
    }
    
    /**
     * Get the json decoded object that contains only the header
     * 
     * @return Object
     */
    public function getDecodedResponse()
    {
        return json_decode($this->getResponse());
    }
    
    /**
    * Format the HTTP response
    * 
    * @return string 
    */
    public function getFormattedResponse()
    {
        if ($this->getCode() == 401) {
            return null;
        }
        
        $response = $this->getResponse();
        list($header, $body) = explode("\r\n\r\n", $response, 2);
        
        $formattedResponse = array(
            'header' => $header,
            'body'   => gzdecode($body)
        );
        
        return $formattedResponse;
    }
    
    /**
     * Return the CURL connection
     * 
     * @return string
     */
    public function getConnection()
    {
        return $this->ch;
    }
    
    /**
     * Get response code
     * 
     * @return int
     */
    public function getCode()
    {
        return curl_getinfo($this->getConnection(), CURLINFO_HTTP_CODE);
    }        
}
