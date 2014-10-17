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
use Validator\Context;

if (php_sapi_name() == 'cli-server' && preg_match('/\.(?:png|jpg|jpeg|gif|css|js)$/', $_SERVER['REQUEST_URI'])) {
    return false;
}

$app = require __DIR__ . '/../app/bootstrap.php';

$app->get('/', function(Bellhop $app) {
    $session = $app['session'];

    /** @var Google_Client $client */
    $client = $app['google.client'];

    $url = $client->createAuthUrl();

    $userInfo = $session->get('user_info');

    $errors = $app['session']->getFlashBag()->get('error');

    if (empty($userInfo) && empty($errors)) {
        return $app->redirect($url);
    }

    return $app->render('index.html.twig', array(
        'authUrl'  => $url,
        'userInfo' => $userInfo,
        'errors'   => $errors,
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

        $context = new Context();
        $context->setUser((array)$user->toSimpleObject());

        if ($validator->isValid($context)) {

            $app['session']->set('user_info', $context->getUser());
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
    $userInfo = $app['session']->get('user_info');

    $validator = $app['validator'];
    try
    {
        $app->log('Open door', ['email' => $userInfo['email']]);

        $context = new Context();
        $context->setUser($userInfo);

        if (!$validator->isValid($context)) {
            $app['session']->getFlashBag()->add('error', 'Your credentials are insufficient');
            $app->log('Insufficient credentials', ['email' => $userInfo['email']], \Monolog\Logger::ERROR);
            return $app->redirect('/logout');
        }

        $sparkClient = $app['spark.door'];
        $sparkClient->exec();
    } catch (InvalidArgumentException $e) {
        $app->log('Communication error', ['exception' => $e, 'email' => $userInfo['email']], \Monolog\Logger::ERROR);
        $app['session']->getFlashBag()->add('error', 'There was a problem with service');
    }

    return $app->redirect('/');
});

$app->run();
