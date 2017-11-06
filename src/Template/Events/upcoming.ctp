<h1 class="page_title">
    <?php echo $titleForLayout; ?>
</h1>

<p style="clear: both;">
    <?php echo $this->Html->link('&larr; Back to Upcoming Event Tags',
        ['controller' => 'events', 'action' => 'upcoming_tags'],
        ['escape' => false]
    ); ?>
</p>

<div id="events">
    <div class="basic_info_col">
        <?php if (empty($events)): ?>
            <p class="alert alert-info">
                Sorry, as far as we know, nothing is happening in Muncie on this date. :(
                <br />
                If you know of an event that we left out, be a pal and
                <?php echo $this->Html->link('tell us about it',
                    ['controller' => 'events', 'action' => 'add', 'm' => $month, 'd' => $day, 'y' => $year]
                ); ?>
            </p>
        <?php else: ?>
            <?php foreach ($events as $date => $date_events): ?>
                <?php foreach ($date_events as $event): ?>
                    <a href="<?php echo Router::url(['controller' => 'events', 'action' => 'view', 'id' => $event['id']]); ?>" id="basic_info_event_<?php echo $event['id']; ?>">
                        <div class="time">
                            <?php echo date('M j, Y', strtotime($date)); ?>
                        </div>
                        <div class="category">
                            <?php if ($event['Category']['name'] == 'General Events'): ?>
                                &nbsp;
                            <?php else: ?>
                                <?php echo $this->Icon->category($event['Category']['name']); ?>
                            <?php endif; ?>
                        </div>
                        <div class="title_location">
                            <span class="fake_link">
                                <?php echo $event['title']; ?>
                            </span>
                            <span class="location">
                                <?php echo $event['location'] ? $event['location'] : '&nbsp;'; ?>
                            </span>
                        </div>
                    </a>
                    <?php
                        $this->Js->buffer("
                            $('basic_info_event_{$event['id']}').observe('click', function (event) {
                                event.stop();
                                selectEvent({$event['id']});
                            });
                        ");
                    ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="detailed_info_col">
        <div id="loading_event" style="display: none;">
            <img src="<?= $fullBaseUrl ?>img/loading.gif" />
        </div>
        <div id="load_event">
            <div id="event_info_placeholder">
                <?php if (!empty($events)): ?>
                    &larr; Click on an event for more info
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<br class="clear" />
