<?php
namespace App\View\Helper;

use Cake\View\Helper;

class TagHelper extends Helper
{
    public $helpers = ['Html', 'Js'];

    private function availableTagsForJs($availableTags)
    {
        $arrayForJson = [];
        if (is_array($availableTags)) {
            foreach ($availableTags as $tag) {
                $arrayForJson[] = [
                    'id' => $tag['id'],
                    'name' => $tag['name'],
                    'selectable' => (boolean) $tag['selectable'],
                    'children' => $this->availableTagsForJs($tag['children'])
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
                    'id' => $tag['id'],
                    'name' => $tag['name']
                ];
            }
        }
        return $arrayForJson;
    }

    /**
     * If necessary, convert selected_tags from an array of IDs to a full array of tag info
     * @param array $selectedTags
     * @return array
     */
    private function formatSelectedTags($selectedTags)
    {
        if (empty($selectedTags)) {
            return [];
        }
        if (is_[$selectedTags[0]]) {
            return $selectedTags;
        }
        $tag = new Tag();
        $retval = [];
        foreach ($selectedTags as $tagId) {
            $result = $tag->find('first', [
                'conditions' => ['id' => $tagId],
                'fields' => ['id', 'name', 'parent_id', 'listed', 'selectable'],
                'contain' => false
            ]);
            $retval[] = $result['Tag'];
        }
        return $retval;
    }

    public function setup($availableTags, $containerId, $selectedTags = [])
    {
        if (!empty($selectedTags)) {
            $selectedTags = $this->formatSelectedTags($selectedTags);
            $this->Js->buffer("
                TagManager.selected_tags = ".$this->Js->object($this->selectedTagsForJs($selectedTags)).";
                TagManager.preselectTags(TagManager.selected_tags);
                ");
        }
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
