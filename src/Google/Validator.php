<?php
/**
 * File containing Validator class
 *
 * @category  App
 * @package   Package
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */

namespace Google;

use Google_Client;
use Google_Service_Oauth2;

/**
 * Short description for class Validator
 *
 * @category  App
 * @package   Google
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */
class Validator
{
    /**
     * @var Google_Client
     */
    private $googleClient;
    /**
     * @var string
     */
    private $hostedDomain;

    /**
     * @param Google_Client $googleClient
     * @param string        $hostedDomain
     */
    public function __construct(Google_Client $googleClient, $hostedDomain)
    {
        $this->googleClient = $googleClient;
        $this->hostedDomain = $hostedDomain;
    }

    /**
     * @param $accessToken
     *
     * @return bool
     */
    public function isValid($accessToken)
    {
        $this->googleClient->setAccessToken($accessToken);

        $service = new Google_Service_Oauth2($this->googleClient);
        $userinfo = $service->userinfo_v2_me;

        $user = $userinfo->get();

        // check if it's astina.ch
        return $user->hd === $this->hostedDomain;
    }
}
