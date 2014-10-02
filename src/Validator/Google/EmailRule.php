<?php
/**
 * File containing EmailRule class
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
 * Short description for class EmailRule
 *
 * @category  App
 * @package   Google
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */
class EmailRule implements RuleInterface
{
    /**
     * @var Google_Client
     */
    private $googleClient;

    /**
     * @var array
     */
    private $emails;

    /**
     * @param Google_Client $googleClient
     * @param array         $emails
     */
    public function __construct(Google_Client $googleClient, array $emails)
    {
        $this->googleClient  = $googleClient;
        $this->emails = $emails;
    }

    public function isValid($accessToken)
    {
        $this->googleClient->setAccessToken($accessToken);

        $service = new Google_Service_Oauth2($this->googleClient);
        $userinfo = $service->userinfo_v2_me;

        $user = $userinfo->get();

        return in_array($user->email, $this->emails);
    }
}
