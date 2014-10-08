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

$app->get('/', function(Bellhop $app) {

    $session = $app['session'];

    /** @var Google_Client $client */
    $client = $app['google.client'];

    $url = $client->createAuthUrl();

    $accessToken = $session->get('access_token');

    $errors = $app['session']->getFlashBag()->get('error');

    if (empty($accessToken) && empty($errors)) {
        return $app->redirect($url);
    }

    return $app->render('index.html.twig', array(
        'authUrl'      => $url,
        'access_token' => $accessToken,
        'errors'       => $errors,
    ));
})->bind('home');

$app->get('/logout', function(Bellhop $app) {
    $app['session']->clear();

    return $app->redirect('/');
});

$app->get('/oauth2callback', function(Request $request, Bellhop $app) {

    $code = $request->get('code');
    if ($code) {
        $app['session']->clear();

        /** @var Google_Client $client */
        $client = $app['google.client'];

        $client->authenticate($code);

        $validator = $app['validator'];

        $service = new Google_Service_Oauth2($client);
        $userinfo = $service->userinfo_v2_me;
        $user = $userinfo->get();

        if ($validator->isValid($client->getAccessToken())) {
            $app['session']->set('access_token', json_decode($client->getAccessToken(), true));
            $app['session']->set('user_info', $user->toSimpleObject());

            $app->log('Access granted', ['email' => $user->email]);
        } else {
            $app['session']->getFlashBag()->add('error', 'Your credentials are insufficient');
            $app->log('Insufficient credentials', ['email' => $user->email], \Monolog\Logger::ERROR);
            return $app->redirect('/logout');
        }
    }

    return $app->redirect('/');
});

$app->get('/api/opendoor', function(Bellhop $app) {
    $accessToken = $app['session']->get('access_token');
    $userInfo = $app['session']->get('user_info');

    $validator = $app['validator'];
    try
    {
        $app->log('Open door', ['email' => $userInfo->email]);

        if (!$validator->isValid(json_encode($accessToken))) {
            $app['session']->getFlashBag()->add('error', 'Your credentials are insufficient');
            $app->log('Insufficient credentials', ['email' => $userInfo->email], \Monolog\Logger::ERROR);
            return $app->redirect('/logout');
        }

        $sparkClient = $app['spark.door'];
        $sparkClient->exec();
    } catch (Google_Auth_Exception $e) {
        $app->log('Insufficient credentials', ['exception' => $e, 'email' => $userInfo->email], \Monolog\Logger::ERROR);
        $app['session']->getFlashBag()->add('error', 'Your credentials are insufficient');
        return $app->redirect('/logout');
    } catch (InvalidArgumentException $e) {
        $app->log('Communication error', ['exception' => $e, 'email' => $userInfo->email], \Monolog\Logger::ERROR);
        $app['session']->getFlashBag()->add('error', $e->getMessage());
    }

    return $app->redirect('/');
});

$app->run();
