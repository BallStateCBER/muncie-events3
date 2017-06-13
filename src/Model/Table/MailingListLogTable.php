<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class MailingListLogTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('mailing_list_log');
        $this->setDisplayField('result');
        $this->belongsTo('MailingList', [
            'foreignKey' => 'recipient_id'
        ]);
    }

    /*	0: Email sent
     * 	1: Error sending email
     * 	2: No events today
     * 	3: No applicable events today
     * 	4: Settings forbid sending email today
     */
    public function addLogEntry($recipientId, $result, $flavor, $testing = false)
    {
        $this->create();
        $testing = $testing ? 1 : 0;
        return $this->save(compact('recipientId', 'result', 'flavor', 'testing'));
    }
}
