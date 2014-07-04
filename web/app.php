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

$app = require __DIR__ . '/../app/bootstrap.php';

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

        $googleValidator = $app['google.validator'];

        if ($googleValidator->isValid($client->getAccessToken())) {
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

    $googleValidator = $app['google.validator'];

    if (!$googleValidator->isValid(json_encode($accessToken))) {
        // @TODO Error message that domain doesn't match
        return $app->redirect('/logout');
    }

    try
    {
        $sparkClient = $app['spark.door'];
        $sparkClient->exec();

        // @TODO success message

    } catch (InvalidArgumentException $e) {
        // @TODO error message
    }

    return $app->redirect('/');
});

$app->run();
