<?php
namespace App\Model\Table;

use Cake\Core\Configure;
use Cake\Mailer\Email;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @property \Cake\ORM\Association\BelongsTo $MailingLists
 * @property \Cake\ORM\Association\HasMany $EventSeries
 * @property \Cake\ORM\Association\HasMany $Events
 * @property \Cake\ORM\Association\HasMany $Images
 * @property \Cake\ORM\Association\HasMany $Tags
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

        $this->addBehavior('Josegonzalez/Upload.Upload', [
            'photo' => [
                'nameCallback' => function (array $data) {
                    $ext = pathinfo($data['name'], PATHINFO_EXTENSION);
                    $salt = Configure::read('profile_salt');
                    $newFilename = md5($data['name'] . $salt);

                    return $newFilename . '.' . $ext;
                },
                'path' => 'webroot' . DS . 'img' . DS . 'users'
            ]
        ]);

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
            ->requirePresence('email', 'create')
            ->notEmpty('email');

        $validator
            ->requirePresence('password', 'create')
            ->notEmpty(['password', 'confirm_password'])
            ->add('confirm_password', [
                'compare' => [
                    'rule' => ['compareWith', 'password'],
                    'message' => 'Your passwords do not match.'
                ]
            ]);

        $validator
            ->allowEmpty('photo', 'update');

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
        return $rules;
    }

    /**
     * send the email from someone's user ID
     *
     * @param int $userId of user
     * @return string
     */
    public function getEmailFromId($userId)
    {
        $query = TableRegistry::get('Users')->find()->select(['email'])->where(['id' => $userId]);
        $result = $query->all();
        $email = $result->toArray();
        $email = implode($email);
        $email = trim($email, '{}');
        $email = str_replace('"email": ', '', $email);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        return $email;
    }

    /**
     * get a user ID from their email address
     *
     * @param string $email of user
     * @return bool
     */
    public function getIdFromEmail($email)
    {
        $result = $this->find()
            ->select(['id'])
            ->where(['email' => $email])
            ->first();
        if ($result) {
            return $result->id;
        }

        return false;
    }

    /**
     * get the security hash for the password reset
     *
     * @param int $userId of user
     * @param string $email to send to
     * @return string
     */
    public function getResetPasswordHash($userId, $email)
    {
        $salt = Configure::read('password_reset_salt');
        $month = date('my');

        return md5($userId . $email . $salt . $month);
    }

    /**
     * send the user their password reset email
     *
     * @param int $userId of user
     * @param string $email to send to
     * @return array
     */
    public function sendPasswordResetEmail($userId, $email)
    {
        $resetPasswordHash = $this->getResetPasswordHash($userId, $email);
        $resetEmail = new Email('default');
        $resetUrl = Router::url([
            'controller' => 'users',
            'action' => 'resetPassword',
            $userId,
            $resetPasswordHash
        ], true);
        $resetEmail
            ->setTo($email)
            ->setSubject('Muncie Events: Reset Password')
            ->setTemplate('forgot_password')
            ->setEmailFormat('both')
            ->setHelpers(['Html', 'Text'])
            ->setViewVars(compact(
                'email',
                'resetUrl'
            ));

        return $resetEmail->send();
    }

    /**
     * get list of images associated with this user
     *
     * @param int $id of user
     * @return array
     */
    public function getImagesList($id)
    {
        $retval = $this->Images->find()
            ->where(['user_id' => $id])
            ->order(['created' => 'DESC'])
            ->toArray();

        return $retval;
    }
}
