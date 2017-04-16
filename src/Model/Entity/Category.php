<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Category Entity
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $weight
 *
 * @property \App\Model\Entity\Event[] $events
 * @property \App\Model\Entity\MailingList[] $mailing_list
 */
class Category extends Entity
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

    public function getAll()
    {
        $result = $this->find('all', [
            'contain' => false,
            'order' => ['weight' => 'ASC']
            ]);
        if (empty($result)) {
            throw new InternalErrorException("No categories found");
        } else {
            Cache::write($cacheKey, $result);
            return $result;
        }
    }
}
