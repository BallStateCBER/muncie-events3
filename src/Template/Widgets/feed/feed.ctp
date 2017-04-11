<?php if (empty($events)): ?>
    <p class="no_events">
        <?php if ($is_ajax): ?>
            No more events found.
        <?php else: ?>
            No upcoming events found.
            <br />
            <?= $this->Html->link('Add an upcoming event', ['controller' => 'events', 'action' => 'add']); ?>
        <?php endif; ?>
    </p>
    <?php $this->Js->buffer("muncieEventsFeedWidget.setNoMoreEvents();"); ?>
<?php else: ?>
    <?php foreach ($events as $date => $days_events): ?>
        <?php
            if ($date == date('Y-m-d')) {
                $day = 'Today';
            } elseif ($date == date('Y-m-d', strtotime('tomorrow'))) {
                $day = 'Tomorrow';
            } else {
                $day = date('l', strtotime($date));
            }
        ?>
        <h2 class="short_date">
            <?= date('M j', strtotime($date)); ?>
        </h2>
        <h2 class="day">
            <?= $day; ?>
        </h2>
        <ul>
            <?php foreach ($days_events as $event): ?>
                <li <?php if (!empty($event['EventsImage'])): ?>class="with_images"<?php endif; ?>>
                    <?php if (!empty($event['EventsImage'])): ?>
                        <?php
                            $image = array_shift($event['EventsImage']);
                            echo $this->Calendar->thumbnail([
                                'filename' => $image['Image']['filename'],
                                'caption' => $image['caption'],
                                'event_id' => $event['Event']['id'],
                                'set' => 'list'
                            ]);
                        ?>
                    <?php endif; ?>
                    <?php $url = Router::url(['controller' => 'events', 'action' => 'view', 'id' => $event['Event']['id']]); ?>
                    <a href="<?= $url; ?>" title="Click for more info" class="event_link" id="event_link_<?= $event['Event']['id']; ?>">
                        <?= $this->Icon->category($event['Category']['name']); ?>
                        <div class="title">
                            <?= $event['Event']['title']; ?>
                        </div>
                        <div class="when_where">
                            <?= date('g:ia', strtotime($event['Event']['time_start'])); ?>
                            @
                            <?= $event['Event']['location'] ? $event['Event']['location'] : '&nbsp;'; ?>
                        </div>
                    </a>
                    <?php if (!empty($event['EventsImage'])): ?>
                        <div class="hidden_images">
                            <?php foreach ($event['EventsImage'] as $image): ?>
                                <?= $this->Calendar->thumbnail([
                                    'filename' => $image['Image']['filename'],
                                    'caption' => $image['caption'],
                                    'event_id' => $event['Event']['id'],
                                    'set' => 'list'
                                ]); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>

    <?php $this->Js->buffer("
        muncieEventsFeedWidget.setNextStartDate('$nextStartDate');
        muncieEventsFeedWidget.prepareLinks([".implode(',', $eventIds)."]);
    "); ?>
<?php endif; ?>
