<?php
namespace App\Model\Table;

use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Categories Model
 *
 * @property \App\Model\Table\EventsTable|\Cake\ORM\Association\HasMany $Events
 * @property \App\Model\Table\MailingListTable|\Cake\ORM\Association\BelongsToMany $MailingList
 * @property \Cake\ORM\Table|\Cake\ORM\Association\HasMany $CategoriesMailingList
 * @method \App\Model\Entity\Category get($primaryKey, $options = [])
 * @method \App\Model\Entity\Category newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Category[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Category|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Category|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Category patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Category[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Category findOrCreate($search, callable $callback = null, $options = [])
 */
class CategoriesTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('categories');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->hasMany('Events', [
            'foreignKey' => 'category_id'
        ]);
        $this->belongsToMany('MailingList', [
            'foreignKey' => 'category_id',
            'targetForeignKey' => 'mailing_list_id',
            'joinTable' => 'categories_mailing_list'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->requirePresence('id', 'update');

        $validator
            ->requirePresence('name', 'create')
            ->minLength('name', 1);

        $validator
            ->requirePresence('slug', 'create')
            ->minLength('slug', 1);

        $validator
            ->integer('weight')
            ->requirePresence('weight', 'create');

        return $validator;
    }

    /**
     * Returns the name of the specified category, or FALSE if none can be found
     *
     * @param int $categoryId Category record ID
     * @return string|bool
     */
    public function getName($categoryId)
    {
        $result = $this->find()
            ->select('name')
            ->where(['id' => $categoryId])
            ->first();
        if (empty($result)) {
            return false;
        }

        return $result['name'];
    }

    /**
     * Returns category IDs that have associated future or past events
     *
     * @param string $direction Either 'future' or 'past'
     * @return array $retval
     */
    public function getCategoriesWithEvents($direction = 'future')
    {
        $retval = [];
        $categoriesTable = TableRegistry::getTableLocator()->get('Categories');
        $dateComparison = ($direction == 'future') ? '>=' : '<';
        foreach ($categoriesTable->find('list') as $categoryId => $category) {
            $result = $this->Events->find()
                ->select(['id'])
                ->where([
                    function (QueryExpression $exp) {
                        return $exp->isNotNull('approved_by');
                    },
                    'category_id' => $categoryId,
                    "Events.date $dateComparison" => date('Y-m-d')
                ])
                ->limit(1);
            if (!$result->isEmpty()) {
                $retval[] = $categoryId;
            }
        }

        return $retval;
    }
}
