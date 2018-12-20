<?php
namespace App\View\Helper;

use App\Model\Entity\Event;
use Cake\Datasource\ResultSetInterface;
use Cake\ORM\TableRegistry;
use Cake\View\Helper;

/**
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \CakeJs\View\Helper\JsHelper $Js
 */
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
        $this->Tags = TableRegistry::getTableLocator()->get('Tags');
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
        if (is_array($event->tags)) {
            foreach ($event->tags as $tag) {
                $selectedTags[] = [
                    'id' => $tag->id,
                    'name' => $tag->name
                ];
            }
        }
        $this->Js->buffer('TagManager.preselectTags(' . json_encode($selectedTags) . ');');
    }

    /**
     * Sets up the javascript for the tag menu
     *
     * @param string $containerSelector The CSS selector of the 'available tags' container
     * @param Event $event that is being tagged
     * @return void
     */
    public function setup($containerSelector, $event)
    {
        $this->preselectTags($event);
        $this->setAvailableTags($containerSelector);
        $this->Js->buffer("
            $('#new_tag_rules_toggler').click(function(event) {
                event.preventDefault();
                $('#new_tag_rules').slideToggle(200);
            });
        ");
    }

    /**
     * Generates the JS for creating a menu of selectable tags
     *
     * @param string $containerSelector The CSS selector of the 'available tags' container
     * @return void
     */
    private function setAvailableTags($containerSelector)
    {
        $tagsTable = TableRegistry::getTableLocator()->get('Tags');
        $results = $tagsTable->find('threaded')
            ->where(['listed' => 1])
            ->order(['name' => 'ASC'])
            ->all();
        $availableTags = $this->availableTagsToArray($results);

        $this->Js->buffer(
            'TagManager.createTagList(' . json_encode($availableTags) . ', $(\'' . $containerSelector . '\'));'
        );
    }

    /**
     * Takes a threaded resultset of available tags and returns a nested array
     *
     * @param ResultSetInterface $tags ResultSet of tags
     * @return array
     */
    private function availableTagsToArray($tags)
    {
        $retval = [];
        foreach ($tags as $tag) {
            $retval[] = [
                'id' => $tag->id,
                'name' => $tag->name,
                'selectable' => (bool)$tag->selectable,
                'children' => $this->availableTagsToArray($tag->children)
            ];
        }

        return $retval;
    }
}
