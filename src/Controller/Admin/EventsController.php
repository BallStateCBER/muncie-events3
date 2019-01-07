<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\Event;
use App\Model\Entity\User;
use Cake\Controller\Component\AuthComponent;
use Cake\Database\Expression\QueryExpression;

/**
 * Events Controller
 *
 * @property \App\Model\Table\EventsTable $Events
 * @property \Search\Controller\Component\PrgComponent $Prg
 * @property AuthComponent $Auth
 */
class EventsController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     * @throws \Exception
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->deny();
    }

    /**
     * Determines whether or not the user is authorized to make the current request
     *
     * @param User|null $user User entity
     * @return bool
     */
    public function isAuthorized($user = null)
    {
        return ($user['role'] == 'admin');
    }

    /**
     * Marks the specified event as approved by an administrator
     *
     * @return \Cake\Http\Response
     */
    public function approve()
    {
        $ids = $this->request->getParam('pass');
        if (empty($ids)) {
            $this->Flash->error('No events approved because no IDs were specified');

            return $this->redirect('/');
        }
        $seriesToApprove = [];
        foreach ($ids as $id) {
            if (!$this->Events->exists($id)) {
                $this->Flash->error('Cannot approve. Event with ID# ' . $id . ' not found.');
                continue;
            }
            $event = $this->Events->get($id, [
                'contain' => 'EventSeries'
            ]);
            if ($event['event_series']['id']) {
                $seriesId = $event['event_series']['id'];
                $seriesToApprove[$seriesId] = true;
            }

            // Approve & publish it
            $event['approved_by'] = $this->Auth->user('id');
            $event['published'] = 1;

            if ($this->Events->save($event)) {
                $this->Flash->success("Event #$id approved.");
            }
        }
        foreach ($seriesToApprove as $seriesId => $flag) {
            $series = $this->EventSeries->get($seriesId);
            $series['published'] = 1;
            if ($this->EventSeries->save($series)) {
                $this->Flash->success("Event Series #$seriesId approved.");
            }
        }

        return $this->redirect($this->referer());
    }

    /**
     * Shows events needing administrator approval
     *
     * @return void
     */
    public function moderate()
    {
        // Collect all unapproved events
        $unapproved = $this->Events
            ->find('all', [
                'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
                'order' => ['created' => 'ASC']
            ])
            ->where([
                'OR' => [
                    function (QueryExpression $exp) {
                        return $exp->isNull('Events.approved_by');
                    },
                    ['Events.published' => '0']
                ]
            ])
            ->toArray();
        /* Find sets of identical events (belonging to the same series and with the same modified date)
         * and remove all but the first */
        $identicalSeries = [];
        foreach ($unapproved as $k => $event) {
            $event['location_new'] = 1;
            $loc = $this->Events->find()
                ->where(['location' => $event['location']])
                ->andWhere(['user_id !=' => $event['user_id']])
                ->count();
            if ($loc > 1) {
                $event['location_new'] = 0;
            }
            if (!isset($event->series_id)) {
                continue;
            }
            $eventId = $event->id;
            $seriesId = $event->event_series['id'];
            $modified = date('Y-m-d', strtotime($event->modified));
            if (isset($identicalSeries[$seriesId][$modified])) {
                unset($unapproved[$k]);
            }
            $identicalSeries[$seriesId][$modified][] = $eventId;
        }
        $this->set([
            'titleForLayout' => 'Review Unapproved Content',
            'unapproved' => $unapproved,
            'identicalSeries' => $identicalSeries
        ]);
    }
}
