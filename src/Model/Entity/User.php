<?php
namespace App\Model\Entity;

use Cake\Auth\DefaultPasswordHasher;
use Cake\Core\Configure;
use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $name
 * @property string $role
 * @property string $bio
 * @property string $email
 * @property string $password
 * @property int|null $mailing_list_id
 * @property int $facebook_id
 * @property string|null $api_key
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \App\Model\Entity\MailingList $mailing_list
 * @property \App\Model\Entity\EventSeries[] $event_series
 * @property \App\Model\Entity\Event[] $events
 * @property \App\Model\Entity\Image[] $images
 * @property \App\Model\Entity\Tag[] $tags
 */
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

    /**
     * Ensures that email addresses are always in lowercase
     *
     * @param string $email Email address
     * @return string
     */
    protected function _getEmail($email)
    {
        return strtolower($email);
    }
}
