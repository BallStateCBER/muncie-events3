<?php
namespace App\View\Helper;

use Cake\View\Helper;
use Cake\ORM\TableRegistry;

class TagHelper extends Helper
{
    public $helpers = ['Html', 'Js'];

    private function availableTagsForJsPr($availableTags)
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
                    'selectable' => (boolean) $tag->selectable,
                    'children' => $this->availableTagsForJsPr($children)
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
    private function formatSelectedTagsPr($newTags, $event)
    {
        $tag = TableRegistry::get('Tags');
        $eventsTable = TableRegistry::get('Events');
        $retval = [];

        // clear it out first to prevent duplicates
        $oldTags = $eventsTable
            ->EventsTags
            ->find()
            ->where(['event_id' => $event->id])
            ->toArray();

        foreach ($oldTags as $oldTag) {
            $result = $tag->getTagFromId($oldTag->tag_id);
            $eventsTable->Tags->unlink($event, [$result]);
        };

        // $_POST but no data? all tags have been deleted.
        if (empty($newTags) && $_POST) {
            return [];
        }

        // no data but there are previous tags? page is just now being edited.
        if (empty($newTags) && $event->tags) {
            return $event->tags;
        }

        // finally, are there new or remaining tags? link them.
        foreach ($newTags as $tagId) {
            // check for duplicates
            $prevTag = $eventsTable
                ->EventsTags
                ->find()
                ->where(['tag_id' => $tagId])
                ->andWhere(['event_id' => $event->id])
                ->count();

            // proceed if there are no duplicates
            if ($prevTag < 1) {
                $result = $tag->getTagFromId($tagId);
                $eventsTable->Tags->link($event, [$result]);
                $retval[] = $result;
            }
        }
        return $retval;
    }

    public function setup($availableTags, $containerId, $selectedTags = [], $event)
    {
        $newTags = empty($this->request->data['data']['Tags']) ? [] : $this->request->data['data']['Tags'];
        $selectedTags = $this->formatSelectedTagsPr($newTags, $event);

        $this->Js->buffer("
            TagManager.selectedTags = ".$this->Js->object($this->selectedTagsForJsPr($selectedTags)).";
            TagManager.preselectTags(TagManager.selectedTags);
        ");

        $parentTags = [];
        foreach ($availableTags as $tag) {
            if ($tag->parent_id == null || $tag->parent_id == 0) {
                $parentTags[] = $tag;
            }
        }

        $this->Js->buffer("
            TagManager.tags = ".$this->Js->object($this->availableTagsForJsPr($parentTags)).";
            TagManager.createTagList(TagManager.tags, $('#$containerId'));
            $('#new_tag_rules_toggler').click(function(event) {
                event.preventDefault();
                $('#new_tag_rules').slideToggle(200);
            });
        ");
    }
}
