<?php
namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Mailer\Email;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @property \Cake\ORM\Association\BelongsTo $MailingLists
 * @property \Cake\ORM\Association\BelongsTo $Facebooks
 * @property \Cake\ORM\Association\HasMany $EventSeries
 * @property \Cake\ORM\Association\HasMany $Events
 * @property \Cake\ORM\Association\HasMany $Images
 * @property \Cake\ORM\Association\HasMany $Tags
 *
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
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

        $this->setTable('users');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('MailingList', [
            'foreignKey' => 'mailing_list_id'
        ]);
        $this->hasMany('EventSeries', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Events', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Images', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Tags', [
            'foreignKey' => 'user_id'
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
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->requirePresence('bio', 'create')
            ->notEmpty('bio');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmpty('email');

        $validator
            ->requirePresence('password', 'create')
            ->notEmpty(['password', 'confirm_password']);

        $validator
            ->add('confirm_password', [
                'compare' => [
                    'rule' => ['compareWith', 'password'],
                    'message' => 'Your passwords do not match'
                ]
            ]);

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
        $rules->add($rules->isUnique(['email'],
            ['message' => 'This email address is already in use.']
        ));
        #$rules->add($rules->existsIn(['mailing_list_id'], 'MailingList'));
        #$rules->add($rules->existsIn(['facebook_id'], 'Facebooks'));

        return $rules;
    }

    public function getIdFromEmail($email) {
        $query = TableRegistry::get('Users')->find()->select(['id'])->where(['email' => $email]);
        $result = $query->all();
        $id = $result->toArray();
        $id = implode($id);

        if ($id) {
            return $id;
        }
    }

    public function getResetPasswordHash($user_id, $email) {
        $salt = Configure::read('password_reset_salt');
        $month = date('my');
        return md5($user_id.$email.$salt.$month);
    }

    public function sendPasswordResetEmail($user_id, $email) {
        $reset_password_hash = $this->getResetPasswordHash($user_id, $email);
        $reset_email = new Email('default');
        $reset_url = Router::url([
            'controller' => 'users',
            'action' => 'resetPassword',
            $user_id,
            $reset_password_hash
        ], true);
        $reset_email->to($email)
            ->subject('Muncie Events: Reset Password')
            ->template('forgot_password')
            ->emailFormat('both')
            ->helpers(['Html', 'Text'])
            ->viewVars(compact(
                'email_address',
                'reset_url'
            ));
        return $reset_email->send();
    }
}
