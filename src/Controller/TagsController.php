<?php
namespace App\Controller;

/**
 * Tags Controller
 *
 * @property \App\Model\Table\CategoriesTable $Categories
 * @property \App\Model\Table\TagsTable $Tags
 */
class TagsController extends AppController
{
    /**
     * Initialize hook method.
     *
     * @return void
     * @throws \Exception
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow(['index', 'autoComplete']);
    }

    /**
     * Determines whether or not the user is authorized to make the current request
     *
     * @param \App\Model\Entity\User|null $user User entity
     * @return bool
     */
    public function isAuthorized($user = null)
    {
        return true;
    }

    /**
     * tags index
     *
     * @param string $direction of the tags
     * @param string $category of the tags
     * @return void
     */
    public function index($direction = 'future', $category = 'all')
    {
        if ($direction != 'future' && $direction != 'past') {
            $direction = 'future';
        }
        $filters = compact('direction');
        if ($category != 'all') {
            $filters['categories'] = $category;
        }
        $tags = $this->Tags->getWithCounts($filters, 'alpha');
        $tagsByFirstLetter = [];
        foreach ($tags as $tag) {
            $firstLetter = ctype_alpha($tag['name'][0]) ? $tag['name'][0] : 'nonalpha';
            $tagsByFirstLetter[$firstLetter][$tag['name']] = $tag;
        }
        $directionAdjective = ($direction == 'future' ? 'upcoming' : 'past');
        $titleForLayout = 'Tags (';
        $titleForLayout .= ucfirst($directionAdjective);
        $this->loadModel('Categories');
        if ($category != 'all' && $categoryName = $this->Categories->getName($category)) {
            $titleForLayout .= ' ' . str_replace(' Events', '', ucwords($categoryName));
        }
        $titleForLayout .= ' Events)';
        $this->set(compact(
            'titleForLayout',
            'tags',
            'tagsByFirstLetter',
            'direction',
            'directionAdjective',
            'category'
        ));
        $this->set([
            'categories' => $this->Categories->find('list')->toArray(),
            'categoriesWithTags' => $this->Categories->getCategoriesWithEvents($direction),
            'letters' => array_merge(range('a', 'z'), ['nonalpha'])
        ]);
    }

    /**
     * autoComplete method
     *
     * @return void
     */
    public function autoComplete()
    {
        $stringToComplete = filter_input(INPUT_GET, 'term');
        $limit = 10;

        // Tag.name will be compared via LIKE to each of these,
        // in order, until $limit tags are found.
        $likeConditions = [
            $stringToComplete,
            $stringToComplete . ' %',
            $stringToComplete . '%',
            '% ' . $stringToComplete . '%',
            '%' . $stringToComplete . '%'
        ];

        // Collect tags up to $limit
        $tags = [];
        foreach ($likeConditions as $like) {
            if (count($tags) == $limit) {
                break;
            }
            $conditions = ['name LIKE' => $like];
            $results = $this->Tags->find()
                ->where($conditions)
                ->limit($limit - count($tags));
            if (!empty($tags)) {
                foreach (array_keys($tags) as $tag) {
                    $results = $results->andWhere(['id !=' => $tag]);
                }
            }
            $x = 1;
            foreach ($results as $result) {
                $tags[$result->id] = [
                    'label' => $result->name,
                    'value' => $result->id
                ];

                $tag = [
                    'label' => $result->name,
                    'value' => $result->id
                ];
                $this->set([$x => $tag]);
                $x = $x + 1;
            }
        }

        $this->viewBuilder()->setLayout('blank');
    }
}
