<?php
namespace App\View\Helper;

use Cake\View\Helper;

class TagHelper extends Helper
{
    public $helpers = ['Html', 'Js'];

    private function availableTagsForJs($availableTags)
    {
        $array_for_json = [];
        if (is_array($availableTags)) {
            foreach ($availableTags as $tag) {
                $array_for_json[] = [
                    'id' => $tag['id'],
                    'name' => $tag['name'],
                    'selectable' => (boolean) $tag['selectable'],
                    'children' => $this->availableTagsForJs($tag['children'])
                ];
            }
        }
        return $array_for_json;
    }

    private function selectedTagsForJs($selected_tags)
    {
        $array_for_json = [];
        if (is_array($selected_tags)) {
            foreach ($selected_tags as $tag) {
                $array_for_json[] = [
                    'id' => $tag['id'],
                    'name' => $tag['name']
                ];
            }
        }
        return $array_for_json;
    }

    /**
     * If necessary, convert selected_tags from an array of IDs to a full array of tag info
     * @param array $selected_tags
     * @return array
     */
    private function formatSelectedTags($selected_tags)
    {
        if (empty($selected_tags)) {
            return [];
        }
        if (is_[$selected_tags[0]]) {
            return $selected_tags;
        }
        App::uses('Tag', 'Model');
        $Tag = new Tag();
        $retval = [];
        foreach ($selected_tags as $tagId) {
            $result = $Tag->find('first', [
                'conditions' => ['id' => $tagId],
                'fields' => ['id', 'name', 'parent_id', 'listed', 'selectable'],
                'contain' => false
            ]);
            $retval[] = $result['Tag'];
        }
        return $retval;
    }

    public function setup($availableTags, $container_id, $selected_tags = [])
    {
        if (!empty($selected_tags)) {
            $selected_tags = $this->formatSelectedTags($selected_tags);
            $this->Js->buffer("
                TagManager.selected_tags = ".$this->Js->object($this->selectedTagsForJs($selected_tags)).";
                TagManager.preselectTags(TagManager.selected_tags);
                ");
        }
        $this->Js->buffer("
            TagManager.tags = ".$this->Js->object($this->availableTagsForJs($availableTags)).";
            TagManager.createTagList(TagManager.tags, $('#$container_id'));
            $('#new_tag_rules_toggler').click(function(event) {
                event.preventDefault();
                $('#new_tag_rules').slideToggle(200);
            });
            ");
    }
}
