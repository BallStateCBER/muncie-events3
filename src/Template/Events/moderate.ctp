<?php
use Cake\Utility\Inflector;

?>
<h1 class="page_title">
    <?= $titleForLayout; ?>
</h1>
<div id="moderate_events">
    <?php if (empty($unapproved)): ?>
        <p>
            Nothing to approve. Take a break and watch some cat videos.
        </p>
    <?php else: ?>
        <ul>
            <?php foreach ($unapproved as $event): ?>
                <?php
                    $eventId = $event->id;
                    $created = $event->created;
                    $modified = $event->modified;
                    $published = $event->published;
                    $isSeries = isset($event['EventSeries']['id']);

                    if ($isSeries) {
                        $series_id = $event['EventSeries']['id'];
                        $count = count($identicalSeriesMembers[$series_id][$modified]);

                        // If events in a series have been modified, they are separated out
                        $countSeriesParts = count($identicalSeriesMembers[$series_id]);
                        $seriesPartEventIds = $identicalSeriesMembers[$series_id][$modified];
                    }
                ?>
                <li>
                    <ul class="actions">
                        <li>
                            <?php
                                $url = ['controller' => 'events', 'action' => 'approve'];
                                if ($isSeries) {
                                    $url = array_merge($url, $seriesPartEventIds);
                                } else {
                                    $url[] = $eventId;
                                }
                                echo $this->Html->link(
                                    $this->Html->image('icons/tick.png').'Approve'.($published ? '' : ' and publish'),
                                    $url,
                                    ['escape' => false]
                                );
                            ?>
                        </li>
                        <li>
                            <?php
                                if ($isSeries && $count > 1) {
                                    $confirm = 'You will only be editing this event, and not the '.($count - 1).' other '.__n('event', 'events', ($count - 1)).' in this series.';
                                } else {
                                    $confirm = false;
                                }
                                echo $this->Html->link(
                                    $this->Html->image('icons/pencil.png').'Edit',
                                    [
                                        'controller' => 'events',
                                        'action' => 'edit',
                                        'id' => $eventId
                                    ],
                                    ['escape' => false, 'confirm' => $confirm]
                                );
                            ?>
                        </li>
                        <li>
                            <?php
                                $url = ['controller' => 'events', 'action' => 'delete'];
                                if ($isSeries && $count > 1) {
                                    $url = array_merge($url, $seriesPartEventIds);
                                    if ($countSeriesParts > 1) {
                                        $confirm = "All $count events in this part of the series will be deleted.";
                                    } else {
                                        $confirm = "All events in this series will be deleted.";
                                    }
                                    $confirm .= ' Are you sure?';
                                } else {
                                    $url[] = $eventId;
                                    $confirm = 'Are you sure?';
                                }
                                echo $this->Form->postLink(
                                    $this->Html->image('icons/cross.png').'Delete',
                                    $url,
                                    ['escape' => false, 'confirm' => $confirm],
                                    'Are you sure?'
                                );
                            ?>
                        </li>
                    </ul>

                    <?php if (!$published): ?>
                        <p>
                            <span class="unpublished">Not published</span>
                        </p>
                    <?php endif; ?>

                    <table>
                        <?php if ($isSeries): ?>
                            <tr>
                                <th>
                                    Series
                                </th>
                                <td>
                                    <?= $event->EventSeries['title']; ?>
                                    (<?= $count.__n(' event', ' events', $count); ?>)
                                    <?php if ($countSeriesParts > 1 && $created != $modified): ?>
                                        <br />
                                        <strong>
                                            <?= __n('This event has', 'These events have', $count); ?>
                                            been edited since being posted.
                                        </strong>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <th>
                                Submitted
                            </th>
                            <td>
                                <?= date('M j, Y g:ia', strtotime($created)); ?>
                                <?php if ($event->User['id']): ?>
                                    by <?= $this->Html->link(
                                        $event->User['name'],
                                        ['controller' => 'users', 'action' => 'view', 'id' => $event['User']['id']]
                                    ); ?>
                                <?php else: ?>
                                    anonymously
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($created != $modified): ?>
                            <tr>
                                <th>
                                    Updated
                                </th>
                                <td>
                                    <?= date('M j, Y g:ia', strtotime($modified)); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <th>
                                Date
                            </th>
                            <td>
                                <?= date('M j, Y', strtotime($event['Event']['date'])); ?>
                                <?= $this->Calendar->eventTime($event); ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                Category
                            </th>
                            <td>
                                <?= $event->Category['name']; ?>
                            </td>
                        </tr>
                        <?php $vars_to_display = ['title', 'description', 'location', 'location_details', 'address', 'age_restriction', 'cost', 'source']; ?>
                        <?php foreach ($vars_to_display as $var): ?>
                            <?php if (!empty($event->$var)): ?>
                                <tr>
                                    <th>
                                        <?= Inflector::humanize($var); ?>
                                    </th>
                                    <td>
                                        <?= $event->$var; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if (!empty($event['Tags'])): ?>
                            <tr>
                                <th>Tags</th>
                                <td>
                                    <?php
                                        $tags_list = [];
                                        foreach ($event['Tags'] as $tag) {
                                            $tags_list[] = $tag['name'];
                                        }
                                        echo implode(', ', $tags_list);
                                    ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($event['EventsImages'])): ?>
                            <tr>
                                <th>Images</th>
                                <td>
                                    <?php foreach ($event['EventsImages'] as $image): ?>
                                        <?= $this->Calendar->thumbnail('tiny', [
                                            'filename' => $image->filename,
                                            'caption' => $image->caption,
                                            'group' => 'unapproved_event_'.$event['Event']['id']
                                        ]); ?>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
