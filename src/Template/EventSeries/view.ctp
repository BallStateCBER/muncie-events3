<?php
use App\Model\Table\EventsTable;

$this->Events = new EventsTable();

    $userId = $this->request->session()->read('Auth.User.id');
    $userRole = $this->request->session()->read('Auth.User.role');
    $canEdit = $userId && ($userRole == 'admin' || $userId == $eventSeries['user_id']);
?>

<h1 class="page_title">
    <?= $titleForLayout; ?>
</h1>

<div class="event_series">
    <?php if ($canEdit): ?>
        <div class="controls">
            <?= $this->Html->link(
                $this->Html->image('/img/icons/pencil.png').'Edit',
                ['controller' => 'eventSeries', 'action' => 'edit', $eventSeries->id],
                ['escape' => false]
            ); ?>
        </div>
    <?php endif; ?>

    <?php
        $dividedEvents = ['upcoming' => [], 'past' => []];
        foreach ($eventSeries->events as $key => $event) {
            if (date('Y-m-d', strtotime($event->start)) < date('Y-m-d')) {
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
            <?= ucwords($section); ?> Events
        </h2>
        <table>
            <tbody>
                <?php foreach ($events as $key => $event): ?>
                    <?php
                        $dst = $this->Events->setDaylightSavings($event->start->format('Y-m-d'));
                    ?>
                    <tr>
                        <td>
                            <?= date('M j, Y', strtotime($event->start . $dst)); ?>
                        </td>
                        <td>
                            <?= date('g:ia', strtotime($event->start . $dst)); ?>
                        </td>
                        <td>
                            <?= $this->Html->link($event['title'],
                                ['controller' => 'events', 'action' => 'view', 'id' => $event->id]
                            ); ?>
                        </td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    <?php endforeach; ?>

    <p class="author">
        <?php if (isset($eventSeries->user['name'])): ?>
            Author:
            <?= $this->Html->link($eventSeries->user['name'], [
                'controller' => 'users', 'action' => 'view', 'id' => $eventSeries->user['id']
            ]); ?>
        <?php else: ?>
            This event series was posted anonymously.
        <?php endif; ?>
    </p>
</div>
