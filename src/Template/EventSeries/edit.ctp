<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\EventSeries $eventSeries
 */
    /* TO DO:
     *         If event->delete is checked, have confirmation dialogue box pop up upon hitting submit
     */
     echo $this->Html->script('event_form.js');
?>
<h1 class="page_title">
    <?= $titleForLayout; ?>
</h1>

<p class="alert alert-info">
    Here, you can edit the name of your event series and edit basic information about each event.
    To edit other details of
    <?= $this->Html->link(
        'your events',
        [
            'controller' => 'events',
            'action' => 'mine'
        ]
    ); ?>, you'll have to go to each event's individual edit page.
</p>

<?= $this->Form->create('eventSeries'); ?>
<table class="event_form event_series_form">
    <tbody>
        <tr>
            <th>Series</th>
            <td><?php
                echo $this->Form->input('title', [
                    'label' => false,
                    'class' => 'form-control',
                    'div' => false,
                    'value' => $eventSeries['title']
                ]);
            ?></td>
        </tr>
        <tr>
            <th>Events</th>
            <td>
                <?php if (empty($eventSeries->events)): ?>
                    Weird. This event series doesn't actually have any events linked to it.
                <?php else: ?>
                    <table id="events_in_series">
                        <tbody>
                            <?php $x = 0; ?>
                            <?php foreach ($eventSeries->events as $event): ?>
                                <?php
                                    $this->Form->setTemplates([
                                        'select' => '<select name="{{name}}" {{attrs}}>{{content}}</select>'
                                    ]);
                                ?>
                                <tr class="display" id="eventinseries_display_<?= $event['id']; ?>">
                                    <td class="action">
                                        <a href="#" class="toggler" data-event-id="<?= $event['id']; ?>">
                                            Edit
                                        </a>
                                    </td>
                                    <td class="date" id="eventinseries_display_<?= $event['id']; ?>_date">
                                        <?= date('M j, Y', strtotime($event['date'])); ?>
                                    </td>
                                    <td class="time" id="eventinseries_display_<?= $event['id']; ?>_time">
                                        <?= date('h:ia', strtotime($event['time_start'])); ?>
                                    </td>
                                    <td class="title" id="eventinseries_display_<?= $event['id']; ?>_title">
                                        <?= $event['title']; ?>
                                    </td>
                                </tr>
                                <tr class="edit" id="eventinseries_edit_<?= $event['id']; ?>" style="display: none;">
                                    <td class="action">
                                        <a href="#" class="toggler" data-event-id="<?= $event['id']; ?>">
                                            Done
                                        </a>
                                    </td>
                                    <td colspan="3">
                                        <table class="edit_event_in_series">
                                            <tr>
                                                <th>Date</th>
                                                <td>
                                                    <?= $this->Form->date('events.'.$x.'.date', [
                                                        'label' => false,
                                                        'maxYear' => date('Y') + 1,
                                                        'year' => [
                                                            'class' => 'form-control event_time_form',
                                                            'id' => ''.$event['id'].'year'
                                                        ],
                                                        'month' => [
                                                            'class' => 'form-control event_time_form',
                                                            'id' => ''.$event['id'].'month'
                                                        ],
                                                        'day' => [
                                                            'class' => 'form-control event_time_form',
                                                            'id' => ''.$event['id'].'day'
                                                        ],
                                                        'default' => $event['date']
                                                    ]); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Time</th>
                                                <td>
                                                    <?= $this->Form->time('events.'.$x.'.time_start', [
                                                        'label' => false,
                                                        'interval' => 5,
                                                        'timeFormat' => '12',
                                                        'hour' => [
                                                            'class' => 'form-control event_time_form',
                                                            'id' => ''.$event['id'].'hour'
                                                        ],
                                                        'minute' => [
                                                            'class' => 'form-control event_time_form',
                                                            'id' => ''.$event['id'].'minute'
                                                        ],
                                                        'meridian' => [
                                                            'class' => 'form-control event_time_form',
                                                            'id' => ''.$event['id'].'meridian'
                                                        ],
                                                        'default' => $event['time_start']
                                                    ],
                                                    'form-control event_time_form'); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Title</th>
                                                <td>
                                                    <?= $this->Form->input('events.'.$x.'.title', [
                                                        'class' => 'form-control',
                                                        'id' => ''.$event['id'].'title',
                                                        'label' => false,
                                                        'default' => $event['title']
                                                    ]); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>
                                                    <label for="eventinseries_delete_<?= $event['id']; ?>">Delete</label>
                                                </th>
                                                <td>
                                                    <?= $this->Form->checkbox('events.'.$x.'.delete', [
                                                        'id' => 'eventinseries_delete_'.$event['id'],
                                                        'class' => 'delete_event',
                                                        'data-event-id' => $event['id']
                                                    ]); ?>
                                                    <?= $this->Form->hidden('events.'.$x.'.edited', [
                                                        'id' => 'eventinseries_edited_'.$event['id'],
                                                        'value' => 0
                                                    ]); ?>
                                                    <?= $this->Form->hidden('events.'.$x.'.id', [
                                                        'value' => $event['id']
                                                    ]); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <?php $x = $x + 1; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>Delete series</th>
            <td>
                <div class="alert alert-danger">
                    <?= $this->Form->checkbox('delete', [
                        'id' => 'event_series_delete_confirm'
                    ]); ?>
                    This will <strong>delete literally all of the events</strong> in the series!
                </div>
            </td>
        </tr>
        <tr>
            <th></th>
            <td>
                <?= $this->Form->submit('Update Series', [
                    'class' => 'btn'
                ]); ?>
                <?= $this->Form->end(); ?>
            </td>
    </tbody>
</table>

<?php
    $this->Js->buffer("
        event_ids = " . json_encode($eventIds) . ";
        setup_eventseries_edit_form();
    ");
?>
