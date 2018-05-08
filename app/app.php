<?php

use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\Request;

// Register global error and exception handlers
ErrorHandler::register();
ExceptionHandler::register();

// Register service providers.
$app->register(new Silex\Provider\AssetServiceProvider(), array(
    'assets.version' => 'v1'
));
$app->register(new Silex\Provider\CsrfServiceProvider());
$app->register(new Silex\Provider\DoctrineServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../templates',
));
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\LocaleServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());

// Register services.
$app['dao.agency'] = function ($app) {
    return new RetardTransilien\DAO\AgencyDAO($app['db']);
};
$app['dao.param'] = function ($app) {
    return new RetardTransilien\DAO\ParamDAO($app['db']);
};
$app['dao.stop'] = function ($app) {
    return new RetardTransilien\DAO\StopDAO($app['db']);
};
$app['dao.trip'] = function ($app) {
    return new RetardTransilien\DAO\TripDAO($app['db']);
};
$app['dao.uic'] = function ($app) {
    return new RetardTransilien\DAO\UicDAO($app['db']);
};
$app['dao.incident'] = function ($app) {
    return new RetardTransilien\DAO\IncidentDAO($app['db']);
};
$app['dao.ongoing'] = function ($app) {
    return new RetardTransilien\DAO\OngoingDAO($app['db']);
};


// Error handler
$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong.';
    }
    return $app['twig']->render('error.html.twig', array(
        'routeShortName' => $app['retardtransilien']['route_short_name'],
        'message' => $message,
    ));
});

