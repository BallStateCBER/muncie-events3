<?php
namespace App\View\Helper;

use Cake\View\Helper;
use Cake\ORM\TableRegistry;

class TagHelper extends Helper
{
    public $helpers = ['Html', 'Js'];

    private function availableTagsForJs($availableTags)
    {
        $arrayForJson = [];
        if (is_array($availableTags)) {
            foreach ($availableTags as $tag) {
                $arrayForJson[] = [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'selectable' => (boolean) $tag->selectable,
                    'children' => $this->availableTagsForJs($tag->children)
                ];
            }
        }
        return $arrayForJson;
    }

    private function selectedTagsForJs($selectedTags)
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
    private function formatSelectedTags($selectedTags, $previousTags, $eventId)
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
            $event = $eventsTable->find()
                ->select(['tag' => 'tag_id'])
                ->where([
                    'event_id' => $eventId,
                    'tag_id' => $tagId
                ])
                ->first();
            if ($result->id != $event->tag) {
                $retval[] = $result;
            }
        }
        if ($previousTags) {
            $retval += $previousTags;
        }
        return $retval;
    }

    public function setup($availableTags, $containerId, $selectedTags = [], $previousTags, $eventId)
    {
        if (!empty($selectedTags)) {
            $selectedTags = $this->formatSelectedTags($selectedTags, $previousTags, $eventId);
        }

        if (empty($selectedTags) && (!empty($previousTags))) {
            $selectedTags = $previousTags;
        }

        if (empty($selectedTags) && (empty($previousTags))) {
            $selectedTags = [];
        }

        $eventsTable = TableRegistry::get('Events');
        if (!$eventId) {
            $event = $eventsTable->newEntity();
        }
        if ($eventId) {
            $event = $eventsTable->get($eventId);
            $eventsTable->Tags->link($event, $selectedTags);
        }

        $this->Js->buffer("
            TagManager.selectedTags = ".$this->Js->object($this->selectedTagsForJs($selectedTags)).";
            TagManager.preselectTags(TagManager.selectedTags);
            ");
        $this->Js->buffer("
            TagManager.tags = ".$this->Js->object($this->availableTagsForJs($availableTags)).";
            TagManager.createTagList(TagManager.tags, $('#$containerId'));
            $('#new_tag_rules_toggler').click(function(event) {
                event.preventDefault();
                $('#new_tag_rules').slideToggle(200);
            });
            ");
    }
}
