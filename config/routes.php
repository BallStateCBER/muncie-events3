<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

/**
 * The default class to use for all routes
 *
 * The following route classes are supplied with CakePHP and are appropriate
 * to set as the default:
 *
 * - Route
 * - InflectedRoute
 * - DashedRoute
 *
 * If no call is made to `Router::defaultRouteClass()`, the class used is
 * `Route` (`Cake\Routing\Route\Route`)
 *
 * Note that `Route` does not do any inflections on URLs which will result in
 * inconsistently cased URLs when used with `:plugin`, `:controller` and
 * `:action` markers.
 *
 * Cache: Routes are cached to improve performance, check the RoutingMiddleware
 * constructor in your `src/Application.php` file to change this behavior.
 *
 */
Router::defaultRouteClass(DashedRoute::class);

Router::extensions(['ics']);

Router::scope('/', function (RouteBuilder $routes) {
    // Home
    $routes->connect('/', ['controller' => 'Events', 'action' => 'index']);

    // Categories
    Router::connect(
        "/:slug/",
        ['controller' => 'Events', 'action' => 'category'],
        ['pass' => ['slug']]
    );

    // viewing events
    Router::connect(
        "event/:id",
        ['controller' => 'Events', 'action' => 'view'],
        ['id' => '[0-9]+', 'pass' => ['id']]
    );
    // events actions
    foreach (['approve', 'delete', 'edit', 'edit_series', 'location', 'publish'] as $action) {
        Router::connect(
            "/event/$action/:id",
            ['controller' => 'Events', 'action' => $action],
            ['id' => '[0-9]+', 'pass' => ['id']]
        );
    }
    // location index
    $routes->connect('/past_locations', ['controller' => 'Events', 'action' => 'past_locations']);

    // viewing locations indexes
    $routes->connect(
        '/location/*',
        ['controller' => 'Events', 'action' => 'location']
    );

    // viewing locations indexes
    $routes->connect(
        '/location/:location/:direction*',
        ['controller' => 'Events', 'action' => 'location'],
        ['pass' => ['slug', 'direction']]
    );

    // viewing event series
    Router::connect(
        "event_series/:id",
        ['controller' => 'EventSeries', 'action' => 'view'],
        ['id' => '[0-9]+', 'pass' => ['id']]
    );

    // eventseries actions
    Router::connect(
        "/event_series/edit/:id",
        ['controller' => 'EventSeries', 'action' => 'edit'],
        ['id' => '[0-9]+', 'pass' => ['id']]
    );

    // pages
    $pages = ['about', 'contact', 'terms'];
    foreach ($pages as $page) {
        $routes->connect('/' . $page, ['controller' => 'Pages', 'action' => $page]);
    }

    // search
    $routes->connect('/search', ['controller' => 'Events', 'action' => 'search']);

    // Tag
    Router::connect(
        "/tag/:slug/:direction",
        ['controller' => 'Events', 'action' => 'tag'],
        ['pass' => ['slug', 'direction']]
    );
    // Tag
    Router::connect(
        "/tag/:slug",
        ['controller' => 'Events', 'action' => 'tag'],
        ['pass' => ['slug']]
    );

    // Tags
    Router::scope('/tags', ['controller' => 'Tags'], function (RouteBuilder $routes) {
        $routes->connect('/', ['action' => 'index', 'future']);
        $routes->connect('/past', ['action' => 'index', 'past']);
    });

    // user actions
    $userActions = ['account', 'login', 'logout', 'register'];
    foreach ($userActions as $action) {
        $routes->connect('/' . $action, ['controller' => 'Users', 'action' => $action]);
    }

    // viewing users
    Router::connect(
        "user/:id/*",
        ['controller' => 'Users', 'action' => 'view'],
        ['id' => '[0-9]+', 'pass' => ['id']]
    );

    // widgets
    $routes->connect('/widgets', ['controller' => 'Widgets', 'action' => 'index']);
    Router::scope('/widgets/customize', ['controller' => 'Widgets'], function (RouteBuilder $routes) {
        $routes->connect('/feed', ['action' => 'customizeFeed']);
        $routes->connect('/month', ['action' => 'customizeMonth']);
    });

    // downloadable content
    Router::connect(
        "/event/:id.ics",
        ['controller' => 'Events',
        'action' => 'ics'],
        ['id' => '[0-9]+', 'pass' => ['id']]
    );

    /**
     * Connect catchall routes for all controllers.
     *
     * Using the argument `DashedRoute`, the `fallbacks` method is a shortcut for
     *    `$routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);`
     *    `$routes->connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);`
     *
     * Any route class can be used with this method, such as:
     * - DashedRoute
     * - InflectedRoute
     * - Route
     * - Or your own route class
     *
     * You can remove these routes once you've connected the
     * routes you want in your application.
     */
    $routes->fallbacks(DashedRoute::class);
});

Router::prefix('admin', function (RouteBuilder $routes) {
    $routes->connect('/moderate', ['controller' => 'Events', 'action' => 'moderate']);

    $routes->connect(
        '/event/approve/:id',
        ['controller' => 'Events', 'action' => 'approve'],
        ['id' => '[0-9]+', 'pass' => ['id']]
    );

    $routes->fallbacks(DashedRoute::class);
});
