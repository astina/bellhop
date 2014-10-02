<?php
/**
 * File containing HostedDomainRule class
 *
 * @category  App
 * @package   Validator
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */

namespace Validator\Google;

use Google_Client;
use Google_Service_Oauth2;
use Validator\RuleInterface;

/**
 * Short description for class HostedDomainRule
 *
 * @category  App
 * @package   Google
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */
class HostedDomainRule implements RuleInterface
{
    /**
     * @var Google_Client
     */
    private $googleClient;

    /**
     * @var array
     */
    private $hostedDomains;

    /**
     * @param Google_Client $googleClient
     * @param array         $hostedDomains
     */
    public function __construct(Google_Client $googleClient, array $hostedDomains)
    {
        $this->googleClient  = $googleClient;
        $this->hostedDomains = $hostedDomains;
    }

    /**
     * @param $accessToken
     * @return bool
     */
    public function isValid($accessToken)
    {
        $this->googleClient->setAccessToken($accessToken);

        $service = new Google_Service_Oauth2($this->googleClient);
        $userinfo = $service->userinfo_v2_me;

        $user = $userinfo->get();

        return in_array($user->hd, $this->hostedDomains);
    }
}
