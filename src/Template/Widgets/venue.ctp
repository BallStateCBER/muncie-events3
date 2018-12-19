<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 */

use Cake\Routing\Router;

?>
<?php if (empty($events)): ?>
    <tr>
        <td colspan="0" class="no_events">
            <?php if ($is_ajax): ?>
                No more events found.
            <?php else: ?>
                No upcoming events found.
            <?php endif; ?>
        </td>
    </tr>
<?php else: ?>
    <?php foreach ($events as $date => $days_events): ?>
        <?php foreach ($days_events as $event): ?>
            <?php
                if ($date == date('Y-m-d')) {
                    $day = 'Today';
                } elseif ($date == date('Y-m-d', strtotime('tomorrow'))) {
                    $day = 'Tomorrow';
                } else {
                    $day = date('l', strtotime($date));
                }
                /* <a href="http://themunciescene.com/event/<?php echo $event['Event']['id']; ?>" title="Click for more info" target="_top"> */
            ?>
            <tr>
                <td class="date">
                    <span class="date">
                        <?php echo date('M j', strtotime($date)); ?>
                    </span>
                    <span class="day">
                        <?php echo $day; ?>
                    </span>
                    <span class="time">
                        <?php echo date('g:ia', strtotime($event->time_start)); ?>
                        <?php if ($event->time_end): ?>
                            - <?php echo date('g:ia', strtotime($event->time_end)); ?>
                        <?php endif; ?>
                    </span>
                </td>
                <td class="event_title">
                    <h3 class="title">
                        <?php echo $event->title; ?>
                    </h3>
                    <div class="description">
                        <?php echo $event->description; ?>
                    </div>
                </td>
                <td class="cost">
                    <?php echo $event->cost; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <tr>
        <td colspan="0" id="controls">
            <a href="#" id="more_events">More events...</a>
            <p id="more_events_error" style="display: none;">Error loading more events.</p>
            <img id="more_events_loading" src="/img/loading.gif" style="display: none;"/>
        </td>
    </tr>
    <?php
    $nextStartingDate = date('Y-m-d', strtotime($date.' +1 day'));
    $url = Router::url(['controller' => 'widgets', 'action' => 'venue', urlencode($venueName), $nextStartingDate], true);
    $this->Js->buffer("
        function loadMoreEvents() {
            $('more_events_loading').show();
            $('more_events').hide();
            new Ajax.Updater($('load_events').down('tbody'), '$url', {
                insertion: 'bottom',
                evalScripts: true,
                onSuccess: function() {
                    $('controls').remove();
                },
                onFailure: function() {
                    $('more_events_loading').hide();
                    $('more_events_error').show();
                    $('more_events').show();
                }
            });
        }
        $('more_events').observe('click', function (event) {
            event.stop();
            loadMoreEvents();
        });
    ");
    ?>
<?php endif; ?>
