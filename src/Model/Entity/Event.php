<?php
namespace App\Model\Entity;

use Cake\Http\Exception\InternalErrorException;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

/**
 * Event Entity
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $location
 * @property string $location_details
 * @property string $location_slug
 * @property string $address
 * @property int|null $user_id
 * @property int $category_id
 * @property int|null $series_id
 * @property \Cake\I18n\FrozenDate $date
 * @property \Cake\I18n\FrozenTime $time_start
 * @property \Cake\I18n\FrozenTime|null $time_end
 * @property string $age_restriction
 * @property string $cost
 * @property string $source
 * @property bool $published
 * @property int|null $approved_by
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Category $category
 * @property \App\Model\Entity\EventSeries $series
 * @property \App\Model\Entity\Image[] $images
 * @property \App\Model\Entity\Tag[] $tags
 * @property \App\Model\Entity\EventSeries $event_series
 */
class Event extends Entity
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
     * Automatically sets the location_slug field
     *
     * @param string $location The location name
     * @return string
     */
    protected function _setLocation($location)
    {
        $this->location_slug = $this->getLocationSlug($location);

        return $location;
    }

    /**
     * Returns the slugged version of the location name
     *
     * @param string $location Location name
     * @return string
     */
    private function getLocationSlug($location)
    {
        $locationSlug = strtolower($location);
        $locationSlug = substr($locationSlug, 0, 20);
        $locationSlug = str_replace('/', ' ', $locationSlug);
        $locationSlug = preg_replace("/[^A-Za-z0-9 ]/", '', $locationSlug);
        $locationSlug = str_replace("   ", ' ', $locationSlug);
        $locationSlug = str_replace("  ", ' ', $locationSlug);
        $locationSlug = str_replace(' ', '-', $locationSlug);
        if (substr($locationSlug, -1) == '-') {
            $locationSlug = substr($locationSlug, 0, -1);
        }

        return $locationSlug;
    }

    /**
     * Sets the event to approved if $user (the user submitting the form) is an administrator
     *
     * @param array $user The user submitting the form (not necessarily the original event author)
     * @return void
     * @throws InternalErrorException
     */
    public function autoApprove($user)
    {
        if (isset($user['role']) && $user['role'] == 'admin') {
            if (! isset($user['id'])) {
                throw new InternalErrorException('Cannot approve event. Administrator ID unknown.');
            }
            $this->approved_by = $user['id'];
        }
    }

    /**
     * Sets the event to approved and published if $user (the user submitting the form) is an administrator
     *
     * @param array $user The user submitting the form (not necessarily the original event author)
     * @return void
     * @throws InternalErrorException
     */
    public function autoPublish($user)
    {
        if ($this->userIsAutoPublishable($user)) {
            $this->published = true;
        }
    }

    /**
     * Returns TRUE if the specified user should have their events automatically published
     *
     * @param array $user Array of user record info, empty for anonymous users
     * @return bool
     */
    public function userIsAutoPublishable($user)
    {
        if (!isset($user['id'])) {
            return false;
        }

        if (isset($user['role']) && $user['role'] == 'admin') {
            true;
        }

        // Users who have submitted events that were published by admins have all subsequent events auto-published
        return $this->userHasPublished($user['id']);
    }

    /**
     * Returns TRUE if the user with the specified ID has any associated published events
     *
     * @param int $userId User ID
     * @return bool
     */
    private function userHasPublished($userId)
    {
        $eventsTable = TableRegistry::getTableLocator()->get('Events');

        return $eventsTable->exists([
            'published' => true,
            'user_id' => $userId
        ]);
    }
}
