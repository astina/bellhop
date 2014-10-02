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

if (php_sapi_name() == 'cli-server' && preg_match('/\.(?:png|jpg|jpeg|gif|css|js)$/', $_SERVER['REQUEST_URI'])) {
    return false;
}

$app = require __DIR__ . '/../app/bootstrap.php';

$app->get('/', function(Application $app) {

    $session = $app['session'];

    /** @var Google_Client $client */
    $client = $app['google.client'];

    $url = $client->createAuthUrl();

    $accessToken = $session->get('access_token');

    $errors = $app['session']->getFlashBag()->get('error');

    if (empty($accessToken) && empty($errors)) {
        return $app->redirect($url);
    }

    return $app['twig']->render('index.html.twig', array(
        'authUrl'      => $url,
        'access_token' => $accessToken,
        'errors'       => $errors,
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

        $validator = $app['validator'];

        if ($validator->isValid($client->getAccessToken())) {
            $app['session']->set('access_token', json_decode($client->getAccessToken(), true));
        } else {
            $app['session']->getFlashBag()->add('error', 'Your credentials are insufficient');
            return $app->redirect('/logout');
        }
    }

    return $app->redirect('/');
});

$app->get('/api/opendoor', function(Application $app) {
    $accessToken = $app['session']->get('access_token');

    $validator = $app['validator'];
    try
    {
        if (!$validator->isValid(json_encode($accessToken))) {
            $app['session']->getFlashBag()->add('error', 'Your credentials are insufficient');
            return $app->redirect('/logout');
        }

        $sparkClient = $app['spark.door'];
        $sparkClient->exec();
    } catch (Google_Auth_Exception $e) {
        $app['session']->getFlashBag()->add('error', 'Your credentials are insufficient');
        return $app->redirect('/logout');
    } catch (InvalidArgumentException $e) {
        $app['session']->getFlashBag()->add('error', $e->getMessage());
    }

    return $app->redirect('/');
});

$app->run();
