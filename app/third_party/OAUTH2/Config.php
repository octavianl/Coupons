<?php

/**
 * Config
 *
 * @category Linkshare
 * @package  OAUTH2
 * @author   Weblight <office@weblight.ro>
 * @license  License http://www.weblight.ro/
 * @link     http://www.weblight.ro/
 *
 */

class Config
{
    const PASSWORD = 0;
    const REFRESH = 1;
    
    protected $application_name = 'Linkshare';
    protected $grant_type = array('password', 'refresh_token');
    protected $username = 'thelichking';
    protected $password = 'arthas123';
    protected $client_id = 'D3R3JTfrev1nYyDgWilTsf3TfOIa';
    protected $client_secret = 'b52ePyomXvbC7AYOjhQTT3EGhrEa';
    protected $scope = '2531438';
    protected $url_token = 'https://api.rakutenmarketing.com/token';
    protected $url_advertisers = 'https://api.rakutenmarketing.com/linklocator/1.0/getMerchByAppStatus/approved';
    protected $url_redirect = '';
    
    public function getApplicationName()
    {
        return $this->application_name;
    }
    
    public function getGrantType($type = self::PASSWORD)
    {
        return $this->grant_type[$type];
    }
    public function getUsername()
    {
        return $this->username;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function getClientId()
    {
        return $this->client_id;
    }
    public function getClientSecret()
    {
        return $this->client_secret;
    }
    public function getScope()
    {
        return $this->scope;
    }
    public function getUrlToken()
    {
        return $this->url_token;
    }
    
    public function getUrlAdvertisers()
    {
        return $this->url_advertisers;
    }
    
    public function getUrlRedirect()
    {
        return $this->url_redirect;
    }
    
    public function getCredentials()
    {
        return base64_encode($this->client_id . ':' . $this->client_secret);
    }
}
