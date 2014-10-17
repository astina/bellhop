<?php
/**
 * File containing Validator class
 *
 * @category  App
 * @package   App
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */

use Validator\Context;
use Validator\RuleInterface;

/**
 * Short description for class Validator
 *
 * @category  App
 * @package   App
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */
class Validator
{
    /**
     * @var array
     */
    private $rules;

    /**
     *
     */
    public function __construct()
    {
        $this->rules = array();
    }

    /**
     * @param RuleInterface $rule
     *
     * @return Validator
     */
    public function addRule(RuleInterface $rule)
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * @param Context $context
     *
     * @return bool
     */
    public function isValid(Context $context)
    {
        if ($this->rules === array()) {
            return true;
        }

        foreach ($this->rules as $rule) {
            if ($rule->isValid($context)) {
                return true;
            }
        }

        return false;
    }
}
