<?php
namespace App\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\Mailer\Email;
use Cake\ORM\Entity;
use Cake\Routing\Router;

class User extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array
     */
    protected function _setPassword($password)
    {
        return (new DefaultPasswordHasher)->hash($password);
    }

    public function getIdFromEmail($email)
    {
        $email = strtolower(trim($email));
        $query = $this->find('all', [
            'conditions' => ['Users.email' => $email],
            'limit' => 1
        ]);
        $result = $query->first();
        if (!$result) {
            return false;
        }
        if ($result) {
            return $result['id'];
        }
    }

    public function getResetPasswordHash($userId, $email = null)
    {
        $salt = Configure::read('password_reset_salt');
        $month = date('my');
        return md5($userId.$email.$salt.$month);
    }

    public function sendPasswordResetEmail($userId, $emailAddress)
    {
        $resetPasswordHash = $this->getResetPasswordHash($userId, $emailAddress);
        $email = new Email('default');
        $titleForLayout = 'Reset Password';
        $resetUrl = Router::url([
            'controller' => 'Users',
            'action' => 'resetPassword',
            $userId,
            $resetPasswordHash
        ], true);
        $email->to($emailAddress)
            ->subject('Muncie Events: Reset Password')
            ->template('forgot_password')
            ->emailFormat('both')
            ->helpers(['Html', 'Text'])
            ->viewVars(compact(
                'titleForLayout',
                'emailAddress',
                'resetUrl'
            ));
        return $email->send();
    }
}
