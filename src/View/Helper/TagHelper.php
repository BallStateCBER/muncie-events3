<?php
namespace App\View\Helper;

use Cake\View\Helper;
use Cake\ORM\TableRegistry;

class TagHelper extends Helper
{
    public $helpers = ['Html', 'Js'];

    private function availableTagsForJsPr($availableTags)
    {
        $arrayForJson = [];
        if (is_array($availableTags)) {
            foreach ($availableTags as $tag) {
                $arrayForJson[] = [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'selectable' => (boolean) $tag->selectable,
                    'children' => $this->availableTagsForJsPr($tag->children)
                ];
            }
        }
        return $arrayForJson;
    }

    private function selectedTagsForJsPr($selectedTags)
    {
        $arrayForJson = [];
        if (is_array($selectedTags)) {
            foreach ($selectedTags as $tag) {
                $arrayForJson[] = [
                    'id' => $tag->id,
                    'name' => $tag->name
                ];
            }
        }
        return $arrayForJson;
    }

    /**
     * If necessary, convert selectedTags from an array of IDs to a full array of tag info
     * @param array $selectedTags
     * @return array
     */
    private function formatSelectedTagsPr($selectedTags, $event)
    {
        if (empty($selectedTags)) {
            return [];
        }
        if (is_array($selectedTags[0])) {
            return $selectedTags;
        }

        $tag = TableRegistry::get('Tags');
        $eventsTable = TableRegistry::get('EventsTags');
        $retval = [];
        foreach ($selectedTags as $tagId) {
            $result = $tag->getTagFromId($tagId);
            $retval[] = $result;
        }
        return $retval;
    }

    public function setup($availableTags, $containerId, $selectedTags = [], $event)
    {
        if (!empty($selectedTags)) {
            $selectedTags = $this->formatSelectedTagsPr($selectedTags, $event);
        }

        $eventsTable = TableRegistry::get('Events');
        if ($event->id && $selectedTags != null) {
            $eventsTable->Tags->link($event, $selectedTags);
        }

        $this->Js->buffer("
            TagManager.selectedTags = ".$this->Js->object($this->selectedTagsForJsPr($selectedTags)).";
            TagManager.preselectTags(TagManager.selectedTags);
            ");
        $this->Js->buffer("
            TagManager.tags = ".$this->Js->object($this->availableTagsForJsPr($availableTags)).";
            TagManager.createTagList(TagManager.tags, $('#$containerId'));
            $('#new_tag_rules_toggler').click(function(event) {
                event.preventDefault();
                $('#new_tag_rules').slideToggle(200);
            });
            ");
    }
}
