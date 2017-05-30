<?php
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
                    'div' => false,
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
                            <?php foreach ($eventSeries->events as $event): ?>
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
                                        <?= date('g:ia', strtotime($event['time_start'])); ?>
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
                                                    <?= $this->Form->input('Event.'.$event['id'].'.date', [
                                                        'div' => false,
                                                        'label' => false,
                                                        'type' => 'date',
                                                        'dateFormat' => 'MDY',
                                                        'minYear' => min(date('Y'), substr($event['date'], 0, 4)),
                                                        'maxYear' => date('Y') + 1,
                                                        'default' => $event['date']
                                                    ]); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Time</th>
                                                <td>
                                                    <?= $this->Form->input('Event.'.$event['id'].'.time_start', $options = [
                                                        'label' => false,
                                                        'interval' => 5,
                                                        'div' => false,
                                                        'default' => $event['time_start']
                                                    ]); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Title</th>
                                                <td>
                                                    <?= $this->Form->input('Event.'.$event['id'].'.title', [
                                                        'div' => false,
                                                        'label' => false,
                                                        'style' => 'width: 150px;',
                                                        'default' => $event['title'],
                                                        //'maxLength' => 100
                                                    ]); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>
                                                    <label for="eventinseries_delete_<?= $event['id']; ?>">Delete</label>
                                                </th>
                                                <td>
                                                    <?= $this->Form->checkbox('Event.'.$event['id'].'.delete', [
                                                        'id' => 'eventinseries_delete_'.$event['id'],
                                                        'class' => 'delete_event',
                                                        'data-event-id' => $event['id']
                                                    ]); ?>
                                                    <?= $this->Form->hidden('Event.'.$event['id'].'.edited', [
                                                        'id' => 'eventinseries_edited_'.$event['id'],
                                                        'value' => 0
                                                    ]); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>Delete</th>
            <td>
                <?= $this->Form->checkbox('delete', [
                    'id' => 'event_series_delete_confirm',
                    'after' => '<div class="footnote">Click to delete all events.</div>',
                ]); ?>
            </td>
        </tr>
        <tr>
            <th></th>
            <td>
                <?= $this->Form->submit('Update Series'); ?>
                <?= $this->Form->end(); ?>
            </td>
    </tbody>
</table>

<?php
    $this->Js->buffer("
        event_ids = ".$this->Js->object($eventIds).";
        setup_eventseries_edit_form();
    ");
?>
