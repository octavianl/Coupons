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
    const ALL = 2;
    
    const URL_TOKEN = 'https://api.rakutenmarketing.com/token';
    
    // Legacy method $aux = file_get_contents('http://lld2.linksynergy.com/services/restLinks/getMerchByAppStatus/' . $token . '/' . $statuses[$j - 1]);   
    const URL_ADVERTISERS_APPROVED = 'https://api.rakutenmarketing.com/linklocator/1.0/getMerchByAppStatus/approved';
    const URL_ADVERTISERS_PERM_REJECTED = 'https://api.rakutenmarketing.com/linklocator/1.0/getMerchByAppStatus/perm%20rejected';
    const URL_ADVERTISERS_PERM_REMOVED = 'https://api.rakutenmarketing.com/linklocator/1.0/getMerchByAppStatus/perm%20removed';
    const URL_ADVERTISERS_SELF_REMOVED = 'https://api.rakutenmarketing.com/linklocator/1.0/getMerchByAppStatus/self%20removed';
    const URL_ADVERTISERS_TEMP_REMOVED = 'https://api.rakutenmarketing.com/linklocator/1.0/getMerchByAppStatus/temp%20removed';
    const URL_ADVERTISERS_TEMP_REJECTED = 'https://api.rakutenmarketing.com/linklocator/1.0/getMerchByAppStatus/temp%20rejected';
    const URL_ADVERTISERS_WAIT = 'https://api.rakutenmarketing.com/linklocator/1.0/getMerchByAppStatus/wait';
    
    protected $application_name = 'Linkshare';
    protected $grant_type = array('password', 'refresh_token');
    protected $username = 'thelichking';
    protected $password = 'arthas123';
    protected $client_id = 'D3R3JTfrev1nYyDgWilTsf3TfOIa';
    protected $client_secret = 'b52ePyomXvbC7AYOjhQTT3EGhrEa';
    private   $scope = '2531438'; // Site ID
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
     * Set site id
     * 
     * @return void
     */
    public function setScope($sid)
    {
        $this->scope = $sid;
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
    
    protected function getToken($ci, $sid, $tokenType = self::ALL)
    {        
        $this->setScope($sid); 
        // build a new HTTP POST request
        $request = new CurlApi(self::URL_TOKEN);
        $request->setHeaders($this->getTokenHeaders());
        $request->setPostData($this->getTokenParams());
        $request->send();
        
        // decode the incoming string as JSON
        $responseObj = $request->getDecodedResponse();
        
        // set tokens in cookie
        $ci->load->helper('cookie');
        $ci->input->set_cookie("accessToken", $responseObj->access_token, 3000);
        $ci->input->set_cookie("refreshToken", $responseObj->refresh_token, 2592000);
        $ci->input->set_cookie("siteID", $sid, 2592000);
        
        if ($tokenType == self::PASSWORD) {
            return $responseObj->access_token;
        } elseif ($tokenType == self::REFRESH) {
            return $responseObj->refresh_token;
        } else {            
            return $responseObj;
        }
                                
        //print '<pre>';
        //print_r($responseObj);
                
        /*
            stdClass Object
            (
                [token_type] => bearer
                [expires_in] => 3042
                [refresh_token] => ec03243c5d33d193f1983f69f2ac
                [access_token] => e6f99a58f75753fa8bcbb81a6ff91
            )
         */                
    }
    
    protected function extendAccessToken($ci, $sid, $refreshToken)
    {        
        $this->setScope($sid);
        // build a new HTTP POST request
        $request = new CurlApi(self::URL_TOKEN);
        $request->setHeaders($this->getTokenHeaders());
        $request->setPostData($this->getRefreshTokenParams($refreshToken));
        $request->send();
        
        // decode the incoming string as JSON
        $responseObj = json_decode($request->getResponse());
        
        $ci->input->set_cookie("accessToken", $responseObj->access_token, 3000);
        $ci->input->set_cookie("refreshToken",$responseObj->refresh_token, 2592000);
        $ci->input->set_cookie("siteID", $sid, 2592000);
        
        return $responseObj;
    }                
    
    public function setSiteCookieAndGetAccessToken($ci, $scope = 2531438)
    {
        $ci->load->helper('cookie');
                
        $siteID = $ci->input->cookie('siteID');        
        $accessToken = $ci->input->cookie('accessToken');
                        
        if ($accessToken) {
            if ($scope == $siteID) {
                // ACCESS TOKEN ALREADY SET
            } else {
                // CHANGE SCOPE/SITE ID first
                $tokens = $this->getToken($ci, $scope);
            }
        } else {
            if (!$ci->input->cookie('refreshToken')) {
                // COOKIES empty             
                $tokens = $this->getToken($ci, $scope); 
            } else {
                if ($scope == $siteID) {
                    // EXTEND ACCESS TOKEN
                    $tokens = $this->extendAccessToken($ci, $scope, $ci->input->cookie('refreshToken'));
                } else {
                    // CHANGE SCOPE/SITE ID second
                    $tokens = $this->getToken($ci, $scope);
                }
            }
        }
        
        if (!$accessToken || $scope != $siteID) {
            $accessToken = $tokens->access_token;
        }
        
        return $accessToken;
    }        
}
