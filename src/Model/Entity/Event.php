<?php
namespace App\Model\Entity;

use Cake\I18n\Time;
use Cake\ORM\Entity;

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
 * @property int $user_id
 * @property int $category_id
 * @property int $series_id
 * @property \Cake\I18n\Time $date
 * @property \Cake\I18n\Time $start
 * @property \Cake\I18n\Time $end
 * @property string $age_restriction
 * @property string $cost
 * @property string $source
 * @property bool $published
 * @property int $approved_by
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Category $category
 * @property \App\Model\Entity\EventSeries $series
 * @property \App\Model\Entity\Image[] $images
 * @property \App\Model\Entity\Tag[] $tags
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
     * @param $location
     * @return bool|mixed|null|string|string[]
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
     * Sets the event to approved and published if $user (the user submitting the form) is an administrator
     *
     * @param array $user The user submitting the form (not necessarily the original event author)
     * @return void
     */
    public function autoApprove($user)
    {
        if ($user['role'] == 'admin') {
            $this->approved_by = $user['id'];
            $this->published = true;
        }
    }

    /**
     * Sets the start, end, and date properties
     *
     * @param array $data Results of request->data() from an event form page
     * @return void
     */
    public function setDatesAndTimes($data)
    {
        $time = new \App\Time\Time();
        $this->start = $time->getStartUtc($data['date'], $data['time_start']);
        $this->end = isset($data['time_end']) ?
            $time->getEndUtc($data['date'], $data['time_end'], $data['time_start']) :
            null;
        $this->date = date('Y-m-d', strtotime($data['date']));
    }


}
