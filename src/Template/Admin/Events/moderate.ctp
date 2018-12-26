<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 * @var \App\Model\Entity\Tag $tag
 * @var \App\Model\Entity\Image $image
 * @var array $identicalSeries
 */
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
                    $modified = date('Y-m-d', strtotime($event->modified));
                    $published = $event->published;
                    $isSeries = isset($event->series_id);
                    $seriesPartEventIds = [];

                    if ($isSeries) {
                        $series_id = $event->series_id;
                        $count = count($identicalSeries[$series_id][$modified]);

                        // If events in a series have been modified, they are separated out
                        $countSeriesParts = count($identicalSeries[$series_id]);
                        $seriesPartEventIds = $identicalSeries[$series_id][$modified];
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
                                    $this->Html->image('icons/tick.png', ['alt' => 'Approve']).'Approve'.($published ? '' : ' and publish'),
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
                                    $this->Html->image('icons/pencil.png', ['alt' => 'Edit']).'Edit',
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
                                    $this->Html->image('icons/cross.png', ['alt' => 'Delete']) . 'Delete',
                                    $url,
                                    ['escape' => false, 'confirm' => $confirm]
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
                                    <?= $event->event_series['title']; ?>
                                    (<?= $count.__n(' event', ' events', $count); ?>)
                                    <?php if ($countSeriesParts > 1 && $created != $modified): ?>
                                        <br />
                                        <strong>
                                            <?= __n('This event has', 'These events have', $count); ?>
                                            been edited since being posted.
                                        </strong>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <th>
                                Submitted
                            </th>
                            <td>
                                <?= date('M j, Y g:ia', strtotime($created)); ?>
                                <?php if ($event->user['id']): ?>
                                    by <?= $this->Html->link(
                                        $event->user['name'],
                                        ['controller' => 'users', 'action' => 'view', 'id' => $event->user['id']]
                                    ); ?>
                                <?php else: ?>
                                    anonymously
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($created != $modified && $modified != '1970-01-01'): ?>
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
                                <?= date('M j, Y', strtotime($event->date)); ?>
                                <?= $this->Calendar->eventTime($event); ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                Category
                            </th>
                            <td>
                                <?= $event->category['name']; ?>
                            </td>
                        </tr>
                        <?php $varsToDisplay = ['title', 'description', 'location', 'location_details', 'address', 'age_restriction', 'cost', 'source']; ?>
                        <?php foreach ($varsToDisplay as $var): ?>
                            <?php if (!empty($event->$var)): ?>
                                <tr>
                                    <th>
                                        <?= Inflector::humanize($var); ?>
                                    </th>
                                    <td>
                                        <?= $event->$var; ?>
                                        <?php if ($var == 'location' && $event['location_new'] == 1): ?>
                                            <br /><b style="color:#c70000;">Note: this is a new location. Please review to make sure that the location name, address, and details are correct!</b>
                                        <?php endif ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if (!empty($event->tags)): ?>
                            <tr>
                                <th>Tags</th>
                                <td>
                                    <?php
                                        $tagsList = [];
                                        foreach ($event->tags as $tag) {
                                            $tagsList[] = $tag->name;
                                        }
                                        echo implode(', ', $tagsList);
                                    ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($event->images)): ?>
                            <tr>
                                <th>Images</th>
                                <td>
                                    <?php foreach ($event->images as $image): ?>
                                        <?= $this->Calendar->thumbnail('tiny', [
                                            'filename' => $image->filename,
                                            'caption' => $image->caption,
                                            'group' => 'unapproved_event_'.$event->id,
                                            'alt' => $image->caption
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
