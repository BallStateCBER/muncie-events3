<?php
namespace App\Test\TestCase\Controller;

use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\EventsController Test Case
 */
class DatabaseConnectionTest extends IntegrationTestCase
{
    public function testTheDatabaseConnection()
    {
        $connection = ConnectionManager::get('test');
        dd($connection);
    }
}
