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
 * @property \App\Model\Table\EventSeriesTable|\Cake\ORM\Association\HasMany $EventSeries
 * @property \App\Model\Table\EventsTable|\Cake\ORM\Association\HasMany $Events
 * @property \App\Model\Table\ImagesTable|\Cake\ORM\Association\HasMany $Images
 * @property \App\Model\Table\TagsTable|\Cake\ORM\Association\HasMany $Tags
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @property \App\Model\Table\MailingListTable|\Cake\ORM\Association\BelongsTo $MailingList
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, callable $callback = null, $options = [])
 * @mixin \Josegonzalez\Upload\Model\Behavior\UploadBehavior
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
            ->integer('id');

        $validator
            ->requirePresence('name', 'create')
            ->minLength('name', 1);

        $validator
            ->email('email')
            ->requirePresence('email', 'create');

        $validator
            ->requirePresence('password', 'create')
            ->minLength('password', 1)
            ->minLength('confirm_password', 1)
            ->add('confirm_password', [
                'compare' => [
                    'rule' => ['compareWith', 'password'],
                    'message' => 'Your passwords do not match.'
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
        $rules->add($rules->isUnique(['email']));

        return $rules;
    }

    /**
     * Returns true only if the user has previously submitted an event that has been published/approved
     * @param int $userId of user
     * @return bool
     */
    public function getAutoPublish($userId)
    {
        if (!$userId) {
            return false;
        }
        $count = $this->Events->find()
            ->where(['user_id' => $userId])
            ->andWhere(['published' => 1])
            ->andwhere(['approved_by IS NOT' => null])
            ->count();

        return $count > 1;
    }

    /**
     * send the email from someone's user ID
     *
     * @param int $userId of user
     * @return string
     */
    public function getEmailFromId($userId)
    {
        $query = TableRegistry::getTableLocator()->get('Users')->find()->select(['email'])->where(['id' => $userId]);
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
     * getRecentUsers method
     *
     * @return array
     */
    public function getRecentUsers()
    {
        $retval = $this->find()
            ->where(['created <=' => date('Y-m-d H:i:s')])
            ->andWhere(['created >' => date('Y-m-d H:i:s', strtotime('-2 days'))])
            ->andWhere(['password !=' => Configure::read('App.spamPassword')])
            ->toArray();

        return $retval;
    }

    /**
     * getRecentUsersCount method
     *
     * @return int
     */
    public function getRecentUsersCount()
    {
        $retval = $this->find()
           ->where(['created <=' => date('Y-m-d H:i:s')])
           ->andWhere(['created >' => date('Y-m-d H:i:s', strtotime('-2 days'))])
           ->andWhere(['password !=' => Configure::read('App.spamPassword')])
           ->count();

        return $retval;
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
            ->setViewVars(compact(
                'email',
                'resetUrl'
            ));

        $resetEmail->viewBuilder()->setHelpers(['Html', 'Text']);

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

    /**
     * setUserAsSpam
     *
     * @param \App\Model\Entity\User|\Cake\Datasource\EntityInterface $user entity that is spam
     * @return bool
     */
    public function setUserAsSpam($user)
    {
        $user['password'] = Configure::read('App.spamPassword');
        if ($this->save($user)) {
            return true;
        }

        return false;
    }
}
