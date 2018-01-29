<?php
namespace App\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\ORM\Entity;

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
        'id' => false,
        'password' => false
    ];

    /**
     * Hashes the user's password if it doesn't match App.spamPassword
     *
     * @param string $password The plaintext password
     * @return string
     */
    protected function _setPassword($password)
    {
        if ($password == Configure::read('App.spamPassword')) {
            return $password;
        }

        return (new DefaultPasswordHasher)->hash($password);
    }
}
