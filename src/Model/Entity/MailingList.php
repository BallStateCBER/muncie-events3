<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * MailingList Entity
 *
 * @property int $id
 * @property string $email
 * @property bool $all_categories
 * @property bool $weekly
 * @property bool $daily_sun
 * @property bool $daily_mon
 * @property bool $daily_tue
 * @property bool $daily_wed
 * @property bool $daily_thu
 * @property bool $daily_fri
 * @property bool $daily_sat
 * @property bool $new_subscriber
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \Cake\I18n\FrozenTime|null $processed_daily
 * @property \Cake\I18n\FrozenTime|null $processed_weekly
 *
 * @property \App\Model\Entity\Category[] $categories
 * @property \App\Model\Entity\User[] $users
 */
class MailingList extends Entity
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
}
