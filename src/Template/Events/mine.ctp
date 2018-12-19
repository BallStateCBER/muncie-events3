<?php
/**
 * @var \App\View\AppView $this
 */
?>
<h1 class="page_title">
    <?php echo $titleForLayout; ?>
</h1>

<div class="my_events">
    <?php if (empty($events) && empty($series)): ?>
        You don't have any events. Care to
        <?php echo $this->Html->link(
            'submit one',
            ['controller' => 'events', 'action' => 'add']
        ); ?>?
    <?php else: ?>
        <h1>Event Series</h1>
        <table class="my_content">
            <?php foreach ($series as $key => $a_series): $id = $a_series['EventSeries']['id']; ?>
                <tr<?php if ($key % 2 == 1): ?> class="altrow"<?php endif; ?>>
                    <td class="date">
                        <span class="fake_link" onclick="$('myeventseries_<?php echo $id; ?>_events').toggle()">
                            Expand
                        </span>
                    </td>
                    <th>
                        <?php echo $this->Html->link(
                            $a_series['EventSeries']['title'],
                            ['controller' => 'event_series', 'action' => 'view', 'id' => $id]
                        ); ?>
                    </th>
                    <td>
                        <?php echo $this->Html->link(
                            $this->Html->image(
                                '/img/icons/fugue/icons/magnifier.png',
                                ['title' => 'View', 'alt' => 'View']
                            ).' View',
                            ['controller' => 'event_series', 'action' => 'view', 'id' => $id],
                            ['escape' => false]
                        ); ?>
                    </td>
                    <td>
                        <?php echo $this->Html->link(
                            $this->Html->image(
                                '/img/icons/fugue/icons/pencil.png',
                                ['title' => 'Edit', 'alt' => 'Edit']
                            ).' Edit',
                            ['controller' => 'event_series', 'action' => 'edit', 'id' => $id],
                            ['escape' => false]
                        ); ?>
                    </td>
                    <td>
                        <?php echo $this->Html->link(
                            $this->Html->image(
                                '/img/icons/fugue/icons/cross.png',
                                ['title' => 'Delete', 'alt' => 'Delete']
                            ).' Delete',
                            ['controller' => 'event_series', 'action' => 'delete', 'id' => $id],
                            ['escape' => false],
                            'Are you sure you want to remove this event series? All of its events will be permanently deleted.'
                        ); ?>
                    </td>
                </tr>
                <tr id="myeventseries_<?php echo $id; ?>_events" style="display: none;">
                    <td class="expanded">
                        <?php echo $this->Html->image('/img/icons/arrow-turn-000-left.png'); ?>
                    </td>
                    <td colspan="4" class="myeventseries_events">
                        <?php echo $this->element('events/my_events', ['events' => $a_series['Event']]); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h1>Single Events</h1>
        <?php echo $this->element('events/my_events', ['events' => $events]); ?>
    <?php endif; ?>
</div>
