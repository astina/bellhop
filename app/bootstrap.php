<?php
/**
 * File containing ${NAME} class
 *
 * @category  App
 * @package   Package
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */

use Google\Validator;
use Silex\Application;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();

$env = getenv('APP_ENV') ?: 'prod';

// config
$configVars = array(
    'app_dir' => __DIR__,
);

foreach ($configVars as $name => $value) {
    $app[$name] = $value;
}

$app->register(new Igorw\Silex\ConfigServiceProvider($app['app_dir'] . '/config/config.yml.dist', $configVars));

$localConfig = $app['app_dir'] . '/config/config.yml';
if (is_readable($localConfig)) {
    $app->register(new Igorw\Silex\ConfigServiceProvider($localConfig, $configVars));
}

// providers
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => $app['app_dir'] . '/views',
));

$app->register(new Silex\Provider\SessionServiceProvider());

// dependency
/**
 * @param Silex\Application $app
 *
 * @return Google_Client
 */
$app['google.client'] = function(Application $app) {
    $client = new Google_Client();
    $client->setClientId($app['google.client_id']);
    $client->setClientSecret($app['google.client_secret']);
    $client->setRedirectUri($app['google.redirect_uri']);

    $client->addScope(array(
        Google_Service_Oauth2::USERINFO_EMAIL,
//        Google_Service_Oauth2::USERINFO_PROFILE,
//        Google_Service_Plus::USERINFO_EMAIL,
//        Google_Service_Plus::USERINFO_PROFILE,
//        Google_Service_Plus::PLUS_ME,
    ));

    $client->setState('profile');
    $client->setApprovalPrompt('force');

    return $client;
};

$app['google.validator'] = function(Application $app) {
    return new Validator($app['google.client'], $app['google.hosted_domain']);
};

$app['spark.door'] = function(Application $app) {
    return new Spark\Client($app['spark_core.url'], $app['spark_core.access_token']);
};

return $app;
