<?php
namespace App\Controller;

use Cake\Utility\Hash;

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
     * Tag index / cloud page
     *
     * @param string $direction Either 'future' or 'past'
     * @param string|int $category Either 'all' or a category ID
     * @return void
     */
    public function index($direction = 'future', $category = 'all')
    {
        // Filters
        if (!in_array($direction, ['future', 'past'])) {
            $direction = 'future';
        }
        $filters = compact('direction');
        if ($category != 'all') {
            $filters['categories'] = $category;
        }

        $tags = $this->Tags->getWithCounts($filters, 'alpha');

        // Create separate sub-lists of tags according to what character they start with
        $tagsByFirstLetter = [];
        foreach ($tags as $tag) {
            $firstLetter = ctype_alpha($tag['name'][0]) ? $tag['name'][0] : 'nonalpha';
            $tagsByFirstLetter[$firstLetter][$tag['name']] = $tag;
        }

        // Generate the page title, specifying direction and (if applicable) category
        $directionAdjective = ($direction == 'future' ? 'upcoming' : 'past');
        $titleForLayout = 'Tags (' . ucfirst($directionAdjective) . ' Events)';
        $this->loadModel('Categories');
        $categoryName = $category == 'all' ? false : $this->Categories->getName($category);
        if ($categoryName) {
            $categoryName = str_replace(' Events', '', ucwords($categoryName));
            $titleForLayout = str_replace(' Events', " $categoryName Events", $titleForLayout);
        }

        // Create a function for determining each tag's individual font size in the cloud
        $maxCount = max(Hash::extract($tags, '{s}.count'));
        $calculateFontSize = function ($tagCount) use ($maxCount) {
            $minFontSize = 75;
            $maxFontSize = 150;
            $fontSizeRange = $maxFontSize - $minFontSize;
            $fontSize = log($maxCount) == 0
                ? log($tagCount) / 1 * $fontSizeRange + $minFontSize
                : log($tagCount) / log($maxCount) * $fontSizeRange + $minFontSize;

            return round($fontSize, 1);
        };

        $this->set(compact(
            'calculateFontSize',
            'category',
            'direction',
            'directionAdjective',
            'tags',
            'tagsByFirstLetter',
            'titleForLayout'
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
            foreach ($results as $result) {
                $tags[$result->id] = [
                    'label' => $result->name,
                    'value' => $result->id
                ];
            }
        }

        $this->set([
            '_serialize' => ['tags'],
            'tags' => $tags
        ]);
        $this->viewBuilder()->setClassName('Json');
    }
}
