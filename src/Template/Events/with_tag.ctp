<?php
/**
 * @var \App\View\AppView $this
 */
?>
<h3 class="selected_tag_category">
    Events
</h3>

<?php
$this->Paginator->__defaultModel = 'Event';
    $this->Paginator->options = [
        'model' => 'Event',
        'update' => 'tagged_content_links_loading',
        'url' => [
            'controller' => 'events',
            'action' => 'with_tag',
            $tagId
        ],
        'evalScripts' => true
    ];
?>
<?php echo $this->element('paging', ['model' => 'Event', 'options' => ['numbers' => true]]); ?>
<?php if (empty($events)): ?>
    Strange. No events have been tagged with this tag. How did you get here?
<?php else: ?>
    <table class="events_list">
        <?php $i = 0; ?>
        <?php foreach ($events as $id => $event) : ?>
            <tr<?php if ($i % 2 == 1): ?> class="shaded"<?php endif; ?>>
                <td class="date">
                    <?php echo date('M j, Y', strtotime($event['Event']['date'])); ?>
                </td>
                <th class="title">
                    <?php echo $this->Html->link(
                        $event['Event']['title'],
                        ['controller' => 'events', 'action' => 'view', 'id' => $event['Event']['id']]
                    ); ?>
                </th>
            </tr>
        <?php $i++; endforeach; ?>
    </table>
<?php endif; ?>
