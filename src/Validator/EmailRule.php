<?php
/**
 * File containing EmailRule class
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
 * Short description for class EmailRule
 *
 * @category  App
 * @package   Validator
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */
class EmailRule implements RuleInterface
{
    /**
     * @var array
     */
    private $emails;

    /**
     * @param array $emails
     */
    public function __construct(array $emails)
    {
        $this->emails = $emails;
    }

    public function isValid(Context $context)
    {
        $user = $context->getUser();
        if (!isset($user['email'])) {
            return false;
        }
        return in_array($user['email'], $this->emails);
    }
}
