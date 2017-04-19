<?php
namespace App\View\Helper;

use Cake\View\Helper;
use Cake\ORM\TableRegistry;

class TagHelper extends Helper
{
    public $helpers = ['Html', 'Js'];

    private function availableTagsForJs($available_tags)
    {
        $arrayForJson = [];
        if (is_array($available_tags)) {
            foreach ($available_tags as $tag) {
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

    private function selectedTagsForJs($selected_tags)
    {
        $arrayForJson = [];
        if (is_array($selected_tags)) {
            foreach ($selected_tags as $tag) {
                $arrayForJson[] = [
                    'id' => $tag->id,
                    'name' => $tag->name
                ];
            }
        }
        return $arrayForJson;
    }

    /**
     * If necessary, convert selected_tags from an array of IDs to a full array of tag info
     * @param array $selected_tags
     * @return array
     */
    private function formatSelectedTags($selected_tags, $previous_tags, $eventId)
    {
        if (empty($selected_tags)) {
            return [];
        }
        if (is_array($selected_tags[0])) {
            return $selected_tags;
        }
        $tag = TableRegistry::get('Tags');
        $eventsTable = TableRegistry::get('EventsTags');
        $retval = [];
        foreach ($selected_tags as $tagId) {
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
        $retval += $previous_tags;
        return $retval;
    }

    public function setup($available_tags, $containerId, $selected_tags = [], $previous_tags, $eventId)
    {
        if (!empty($selected_tags)) {
            $selected_tags = $this->formatSelectedTags($selected_tags, $previous_tags, $eventId);
        }

        if (empty($selected_tags) && (!empty($previous_tags))) {
            $selected_tags = $previous_tags;
        }

        $eventsTable = TableRegistry::get('Events');
        $event = $eventsTable->get($eventId);
        $eventsTable->Tags->link($event, $selected_tags);

        $this->Js->buffer("
            TagManager.selected_tags = ".$this->Js->object($this->selectedTagsForJs($selected_tags)).";
            TagManager.preselectTags(TagManager.selected_tags);
            ");
        $this->Js->buffer("
            TagManager.tags = ".$this->Js->object($this->availableTagsForJs($available_tags)).";
            TagManager.createTagList(TagManager.tags, $('#$containerId'));
            $('#new_tag_rules_toggler').click(function(event) {
                event.preventDefault();
                $('#new_tag_rules').slideToggle(200);
            });
            ");
    }
}
