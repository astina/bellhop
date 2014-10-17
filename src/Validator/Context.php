<?php
/**
 * File containing Context class
 *
 * @category  App
 * @package   Package
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */

namespace Validator;

/**
 * Short description for class Context
 *
 * @category  App
 * @package   Validator
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */
class Context
{

    /**
     * @var array
     */
    private $user = array();

    /**
     * @return array
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param array $user
     *
     * @return Context
     */
    public function setUser(array $user)
    {
        $this->user = $user;

        return $this;
    }
}
