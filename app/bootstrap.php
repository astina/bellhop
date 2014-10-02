<?php
/**
 * File containing ${NAME} class
 *
 * @category  App
 * @package   Package
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */

use Silex\Application;
use Validator\Google\EmailRule;
use Validator\Google\HostedDomainRule;

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

$app->register(new Silex\Provider\SessionServiceProvider(), array(
    'cookie_lifetime' => 3600 * 24 * 14 // two weeks
));

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
    ));

    $client->setAccessType('online');
    $client->setApprovalPrompt('auto');

    return $client;
};

$app['validator'] = function(Application $app) {
    $validator = new Validator();

    if (!empty($app['rule.google.hosted_domain'])) {
        $rule = new HostedDomainRule($app['google.client'], $app['rule.google.hosted_domain']);
        $validator->addRule($rule);
    }

    if (!empty($app['rule.google.email'])) {
        $rule = new EmailRule($app['google.client'], $app['rule.google.email']);
        $validator->addRule($rule);
    }

    return $validator;
};

$app['spark.door'] = function(Application $app) {
    return new Spark\Client($app['spark_core.url'], $app['spark_core.access_token']);
};

return $app;
