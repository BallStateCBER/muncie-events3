<?php
namespace App\View\Helper;

use App\Model\Entity\Event;
use Cake\ORM\TableRegistry;
use Cake\View\Helper;

class TagHelper extends Helper
{
    public $helpers = ['Html', 'Js'];

    /**
     * create array for json of available tags
     *
     * @param array $availableTags to convert for json
     * @return array
     */
    private function availableTagsForJs($availableTags)
    {
        $this->Tags = TableRegistry::get('Tags');
        $arrayForJson = [];
        if (is_array($availableTags)) {
            foreach ($availableTags as $tag) {
                $children = $this->Tags->find()
                    ->where(['parent_id' => $tag->id])
                    ->order(['name' => 'ASC'])
                    ->toArray();

                $arrayForJson[] = [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'selectable' => (bool)$tag->selectable,
                    'children' => $this->availableTagsForJs($children)
                ];
            }
        }

        return $arrayForJson;
    }

    /**
     * Adds JS that pre-selects tags in the event form
     *
     * @param Event $event Event entity
     * @return void
     */
    private function preselectTags($event)
    {
        $selectedTags = [];
        foreach ($event->tags as $tag) {
            $selectedTags[] = [
                'id' => $tag->id,
                'name' => $tag->name
            ];
        }
        $this->Js->buffer('TagManager.preselectTags(' . json_encode($selectedTags) . ');');
    }

    /**
     * Sets up the javascript for the tag menu
     *
     * @param array $availableTags to be selected
     * @param string $containerId of the tag menu
     * @param Event $event that is being tagged
     * @return void
     */
    public function setup($availableTags, $containerId, $event)
    {
        $this->preselectTags($event);

        $parentTags = [];
        foreach ($availableTags as $tag) {
            if ($tag->parent_id == null || $tag->parent_id == 0) {
                $parentTags[] = $tag;
            }
        }

        $this->Js->buffer("
            TagManager.tags = " . $this->Js->object($this->availableTagsForJs($parentTags)) . ";
            TagManager.createTagList(TagManager.tags, $('#$containerId'));
            $('#new_tag_rules_toggler').click(function(event) {
                event.preventDefault();
                $('#new_tag_rules').slideToggle(200);
            });
        ");
    }
}
