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
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Application();

$env = getenv('APP_ENV') ?: 'prod';

$configVars = array(
    'app_dir' => __DIR__ . '/../app',
);

$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__ . '/../app/config/config.yml.dist', $configVars));

$localConfig = __DIR__ . '/../app/config/config.yml';
if (is_readable($localConfig)) {
    $app->register(new Igorw\Silex\ConfigServiceProvider($localConfig, $configVars));
}

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../app/views',
));
$app->register(new Silex\Provider\SessionServiceProvider());

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
        Google_Service_Oauth2::USERINFO_PROFILE,
        Google_Service_Plus::USERINFO_EMAIL,
        Google_Service_Plus::USERINFO_PROFILE,
        Google_Service_Plus::PLUS_ME,
    ));

    $client->setState('profile');
    $client->setApprovalPrompt('force');

    return $client;
};

$app->get('/', function(Application $app) {
    $session = $app['session'];

    /** @var Google_Client $client */
    $client = $app['google.client'];

    $url = $client->createAuthUrl();

    $accessToken = $session->get('access_token');

    return $app['twig']->render('index.html.twig', array(
        'authUrl'      => $url,
        'access_token' => $accessToken,
    ));
})->bind('home');

$app->get('/logout', function(Application $app) {
    $app['session']->clear();

    return $app->redirect('/');
});

$app->get('/oauth2callback', function(Request $request, Application $app) {

    $code = $request->get('code');
    if ($code) {
        /** @var Google_Client $client */
        $client = $app['google.client'];

        $client->authenticate($code);

        $app['session']->remove('access_token');

        $client->setAccessToken($client->getAccessToken());

        $service = new Google_Service_Oauth2($client);
        $userinfo = $service->userinfo_v2_me;

        $user = $userinfo->get();
        // check if it's astina.ch
        if ($user->hd === $app['google.hosted_domain']) {
            $app['session']->set('access_token', json_decode($client->getAccessToken(), true));
        } else {
            // @TODO Error message that domain doesn't match
        }
    }

    return $app->redirect('/');
});

$app->get('/api/opendoor', function(Application $app) {
    $accessToken = $app['session']->get('access_token');
    /** @var Google_Client $client */
    $client = $app['google.client'];

    $client->setAccessToken(json_encode($accessToken));

    $service = new Google_Service_Oauth2($client);
    $userinfo = $service->userinfo_v2_me;

    $user = $userinfo->get();
    if ($user->hd !== $app['google.hosted_domain']) {
        return $app->redirect('/logout');
    }

    $url = $app['spark_core.url'];
    $fields = array(
        'access_token' => $app['spark_core.access_token'],
    );

    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-type: application/x-www-form-urlencoded'
    ));

    $result = curl_exec($ch);
    $error = curl_error($ch);
    $errorNo = curl_errno($ch);
    curl_close($ch);

    if ($errorNo !== 0) {
        // @TODO error $error


    } else {
        $data = json_decode($result, true);

        if (isset($data['error'])) {
            // @TODO error
        } else {
            // @TODO success
        }
    }

    return $app->redirect('/');
});

$app->run();
