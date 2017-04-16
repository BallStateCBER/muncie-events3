<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * MailingList Model
 *
 * @property \Cake\ORM\Association\HasMany $Users
 * @property \Cake\ORM\Association\BelongsToMany $Categories
 *
 * @method \App\Model\Entity\MailingList get($primaryKey, $options = [])
 * @method \App\Model\Entity\MailingList newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\MailingList[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MailingList|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\MailingList patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MailingList[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\MailingList findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MailingListTable extends Table
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

        $this->setTable('mailing_list');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Users', [
            'foreignKey' => 'mailing_list_id'
        ]);
        $this->belongsToMany('Categories', [
            'foreignKey' => 'mailing_list_id',
            'targetForeignKey' => 'category_id',
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
            ->allowEmpty('id', 'create');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmpty('email');

        $validator
            ->boolean('all_categories')
            ->requirePresence('all_categories', 'create')
            ->notEmpty('all_categories');

        $validator
            ->allowEmpty('categories');

        $validator
            ->boolean('weekly')
            ->requirePresence('weekly', 'create')
            ->notEmpty('weekly');

        $validator
            ->boolean('daily_sun')
            ->requirePresence('daily_sun', 'create')
            ->notEmpty('daily_sun');

        $validator
            ->boolean('daily_mon')
            ->requirePresence('daily_mon', 'create')
            ->notEmpty('daily_mon');

        $validator
            ->boolean('daily_tue')
            ->requirePresence('daily_tue', 'create')
            ->notEmpty('daily_tue');

        $validator
            ->boolean('daily_wed')
            ->requirePresence('daily_wed', 'create')
            ->notEmpty('daily_wed');

        $validator
            ->boolean('daily_thu')
            ->requirePresence('daily_thu', 'create')
            ->notEmpty('daily_thu');

        $validator
            ->boolean('daily_fri')
            ->requirePresence('daily_fri', 'create')
            ->notEmpty('daily_fri');

        $validator
            ->boolean('daily_sat')
            ->requirePresence('daily_sat', 'create')
            ->notEmpty('daily_sat');

        $validator
            ->boolean('new_subscriber')
            ->requirePresence('new_subscriber', 'create')
            ->notEmpty('new_subscriber');

        $validator
            ->dateTime('processed_daily')
            ->allowEmpty('processed_daily');

        $validator
            ->dateTime('processed_weekly')
            ->allowEmpty('processed_weekly');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['email']));

        return $rules;
    }
}
