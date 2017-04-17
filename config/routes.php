<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Core\Plugin;
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
 */
Router::defaultRouteClass(DashedRoute::class);

Router::scope('/', function (RouteBuilder $routes) {
    // home
    $routes->connect('/', ['controller' => 'Events', 'action' => 'index']);

    // events actions
    foreach (['edit', 'edit_series', 'publish', 'approve', 'delete'] as $action) {
        Router::connect(
            "/event/$action/:id",
            ['controller' => 'events', 'action' => $action],
            ['id' => '[0-9]+', 'pass' => ['id']]
        );
    }

    // location index
    $routes->connect('/location/*', ['controller' => 'events', 'action' => 'location']);
    $routes->connect('/past_locations', ['controller' => 'events', 'action' => 'past_locations']);

    // viewing events
    Router::connect("event/:id",
        ['controller' => 'events', 'action' => 'view'],
        ['id' => '[0-9]+', 'pass' => ['id']]
    );

    // Categories
    $category_slugs = ['music', 'art', 'theater', 'film', 'activism', 'general', 'education', 'government', 'sports', 'religion'];
    foreach ($category_slugs as $slug) {
        $routes->connect("/$slug/*", ['controller' => 'events', 'action' => 'category', $slug]);
    }

    // Tag
    Router::connect(
        "/tag/:slug/*",
        ['controller' => 'events', 'action' => 'tag'],
        ['pass' => ['slug']]
    );

    // Tags
    $routes->connect('/tags', ['controller' => 'tags', 'action' => 'index', 'future']);
    $routes->connect('/tags/past', ['controller' => 'tags', 'action' => 'index', 'past']);

    // pages
    $routes->connect('/about', ['controller' => 'Pages', 'action' => 'about']);
    $routes->connect('/contact', ['controller' => 'Pages', 'action' => 'contact']);
    $routes->connect('/terms', ['controller' => 'Pages', 'action' => 'terms']);

    // users actions
    $routes->connect('/login', ['controller' => 'Users', 'action' => 'login']);
    $routes->connect('/register', ['controller' => 'Users', 'action' => 'register']);

    // viewing users
    Router::connect("user/:id",
        ['controller' => 'users', 'action' => 'view'],
        ['id' => '[0-9]+', 'pass' => ['id']]
    );

    // widgets
    Router::connect(
        "/widgets/customize/feed",
        ['controller' => 'widgets', 'action' => 'customize_feed']
    );
    Router::connect(
        "/widgets/customize/month",
        ['controller' => 'widgets', 'action' => 'customize_month']
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

/**
 * Load all plugin routes.  See the Plugin documentation on
 * how to customize the loading of plugin routes.
 */
Plugin::routes();
