<?php
/**
 * File containing ${NAME} class
 *
 * @category  App
 * @package   Package
 * @author    Fredi Pevcin <fpevcin@astina.ch>
 * @copyright 2014 Astina AG (http://astina.ch)
 */

use Validator\Google\EmailRule;
use Validator\Google\HostedDomainRule;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Bellhop();

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
    'session.storage.save_path' => $app['app_dir'] . '/data/session',
    'session.storage.options' => array(
        'session.gc_maxlifetime'    => 3600 * 24 * 14, // two weeks
        'session.cookie_lifetime'   => 3600 * 24 * 14, // two weeks
    )
));

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/logs/app.log',
    'monolog.name' => 'bellhop',
    'monolog.level' => 'warning',

));

// dependency
/**
 * @param Silex\Application $app
 *
 * @return Google_Client
 */
$app['google.client'] = function(Bellhop $app) {
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

$app['validator'] = function(Bellhop $app) {
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

$app['spark.door'] = function(Bellhop $app) {
    return new Spark\Client($app['spark_core.url'], $app['spark_core.access_token']);
};

if (!empty($app['error_notify'])) {
    $app['monolog']->pushHandler(new \Monolog\Handler\NativeMailerHandler($app['error_notify'], 'Bellhop error', 'bellhop'));
}

return $app;
