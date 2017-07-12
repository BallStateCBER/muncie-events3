<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         3.3.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Test\TestCase;

use App\Application;
use Cake\Routing\Router;
use Cake\TestSuite\IntegrationTestCase;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;

/**
 * ApplicationTest class
 */
class AppControllerTest extends IntegrationTestCase
{
    public function setUp()
    {
        $id = '496726620385625';
        $secret = '8c2bca1961dbf8c8bb92484d9d2dd318';
        FacebookSession::setDefaultApplication($id, $secret);

        $redirectUrl = Router::url(['controller' => 'Users', 'action' => 'login'], true);
        $helper = new FacebookRedirectLoginHelper($redirectUrl);
        $helper->disableSessionStatusCheck();
    }
}
