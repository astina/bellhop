<?php
/**
 * File containing HostedDomainRule class
 *
 * @category  App
 * @package   Validator
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */

namespace Validator;

use Google_Client;
use Google_Service_Oauth2;
use Validator\Context;
use Validator\RuleInterface;

/**
 * Short description for class HostedDomainRule
 *
 * @category  App
 * @package   Validator
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */
class HostedDomainRule implements RuleInterface
{
    /**
     * @var array
     */
    private $hostedDomains;

    /**
     * @param array         $hostedDomains
     */
    public function __construct(array $hostedDomains)
    {
        $this->hostedDomains = $hostedDomains;
    }

    /**
     * @param Context $context
     * @return bool
     */
    public function isValid(Context $context)
    {
        $user = $context->getUser();
        if (!isset($user['hd'])) {
            return false;
        }

        return in_array($user['hd'], $this->hostedDomains);
    }
}
