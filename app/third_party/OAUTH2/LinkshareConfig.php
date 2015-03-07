<?php

/**
 * Linkshare Config
 *
 * @category Linkshare
 * @package  OAUTH2
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */

class LinkshareConfig
{
    const PASSWORD = 0;
    const REFRESH = 1;
    
    const URL_TOKEN = 'https://api.rakutenmarketing.com/token';
    const URL_ADVERTISERS = 'https://api.rakutenmarketing.com/linklocator/1.0/getMerchByAppStatus/approved';
    
    protected $application_name = 'Linkshare';
    protected $grant_type = array('password', 'refresh_token');
    protected $username = 'thelichking';
    protected $password = 'arthas123';
    protected $client_id = 'D3R3JTfrev1nYyDgWilTsf3TfOIa';
    protected $client_secret = 'b52ePyomXvbC7AYOjhQTT3EGhrEa';
    protected $scope = '2531438';
    // not used for now but in the Linkshare documentation it says to be somewhat required
    protected $url_redirect = '';
    
    /**
     * Get the application name
     * 
     * @return string
     */
    public function getApplicationName()
    {
        return $this->application_name;
    }
    
    /**
     * Set the grant type
     * 
     * @param type int The grant type
     * 
     * @return string
     */
    public function getGrantType($type = self::PASSWORD)
    {
        return $this->grant_type[$type];
    }
    
    /**
     * Get the username
     * 
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * Get password
     * 
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
    
    /**
     * Get the client id
     * 
     * @return string
     */
    public function getClientId()
    {
        return $this->client_id;
    }
    
    /**
     * Get client secret code
     * 
     * @return string
     */
    public function getClientSecret()
    {
        return $this->client_secret;
    }
    
    /**
     * Get the scope
     * 
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Get url redirect
     * 
     * @return string
     */
    public function getUrlRedirect()
    {
        return $this->url_redirect;
    }
    
    /**
     * Get credentials
     * 
     * @return string
     */
    public function getCredentials()
    {
        return base64_encode($this->client_id . ':' . $this->client_secret);
    }
    
    /**
     * Get minimal headers
     * 
     * @return array
     */
    public function getMinimalHeaders($accessToken)
    {
        return array(
            "Accept: application/json, text/javascript, */*; q=0.01",
            "Accept-Encoding: gzip, deflate, sdch",
            "Accept-Language: en-US,en;q=0.8,ro;q=0.6",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "Authorization: Bearer " . $accessToken
        );
    }
    
    /**
     * Get token headers
     * 
     * @return array
     */
    public function getTokenHeaders()
    {
        return array(
            "Content-type: application/x-www-form-urlencoded",
            "Accept: */*",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "Authorization: Basic " . $this->getCredentials()
        );
    }
    
    /**
     * Get token params
     * 
     * @return array
     */
    public function getTokenParams()
    {
        return array(
            //"redirect_uri" => $this->getUrlRedirect(),
            "grant_type" => $this->getGrantType(self::PASSWORD),
            "username" => $this->getUsername(),
            "password" => $this->getPassword(),
            "scope" => $this->getScope()
        );
    }
    
    /**
     * Get refresh token params
     * 
     * @return array
     */
    public function getRefreshTokenParams($refreshToken)
    {
        return array(
            'grant_type' => $this->getGrantType(self::REFRESH),
            'refresh_token' => $refreshToken,
            'scope' => 'PRODUCTION'
        );
    }
}
