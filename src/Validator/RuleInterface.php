<?php
/**
 * File containing RuleInterface class
 *
 * @category  App
 * @package   Validator
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */

namespace Validator;

/**
 * Short description for class RuleInterface
 *
 * @category  App
 * @package   Validator
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */
interface RuleInterface
{
    public function isValid(Context $context);
}
