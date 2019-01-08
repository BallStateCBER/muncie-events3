<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\EventSeries $eventSeries
 * @var \App\Model\Entity\Event $event
 */
use App\Model\Table\EventsTable;

$this->Events = new EventsTable();
$userId = $this->request->getSession()->read('Auth.User.id');
$userRole = $this->request->getSession()->read('Auth.User.role');
$canEdit = $userId && ($userRole == 'admin' || $userId == $eventSeries['user_id']);
?>

<h1 class="page_title">
    <?= $titleForLayout ?>
</h1>

<div class="event_series">
    <?php if ($canEdit): ?>
        <div class="controls">
            <?= $this->Html->link(
                $this->Html->image('/img/icons/pencil.png').'Edit',
                ['controller' => 'eventSeries', 'action' => 'edit', $eventSeries->id],
                ['escape' => false]
            ) ?>
        </div>
    <?php endif; ?>

    <?php
        $dividedEvents = ['upcoming' => [], 'past' => []];
        foreach ($eventSeries->events as $key => $event) {
            if (date('Y-m-d', strtotime($event->date)) < date('Y-m-d')) {
                $dividedEvents['past'][] = $event;
            } else {
                $dividedEvents['upcoming'][] = $event;
            }
        }
        rsort($dividedEvents['past']);
    ?>
    <?php foreach ($dividedEvents as $section => $events): ?>
        <?php if (empty($events)) {
        continue;
    } ?>
        <h2>
            <?= ucwords($section) ?> Events
        </h2>
        <table>
            <tbody>
                <?php foreach ($events as $key => $event): ?>
                    <tr>
                        <td>
                            <?= $this->Calendar->date($event) ?>
                        </td>
                        <td>
                            <?= $this->Calendar->time($event) ?>
                        </td>
                        <td>
                            <?= $this->Html->link(
                                $event['title'],
                                [
                                    'plugin' => false,
                                    'prefix' => false,
                                    'controller' => 'Events',
                                    'action' => 'view',
                                    'id' => $event->id
                                ]
                            ) ?>
                        </td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    <?php endforeach; ?>

    <p class="author">
        <?php if (isset($eventSeries->user['name'])): ?>
            Author:
            <?= $this->Html->link(
                $eventSeries->user['name'],
                [
                    'plugin' => false,
                    'prefix' => false,
                    'controller' => 'Users',
                    'action' => 'view',
                    'id' => $eventSeries->user['id']
                ]
            ) ?>
        <?php else: ?>
            This event series was posted anonymously.
        <?php endif; ?>
    </p>
</div>
