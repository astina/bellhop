<?php
/**
 * File containing Bellhop class
 *
 * @category  App
 * @package   App
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */

use Silex\Application;

/**
 * Short description for class Bellhop
 *
 * @category  App
 * @package   App
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */
class Bellhop extends Application
{
    use Application\TwigTrait;
    use Application\MonologTrait;
}
