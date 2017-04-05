<h1 class="page_title">
    <?php echo $title_for_layout; ?>
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
                    $event_id = $event['Event']['id'];
                    $created = $event['Event']['created'];
                    $modified = $event['Event']['modified'];
                    $published = $event['Event']['published'];
                    $is_series = isset($event['EventSeries']['id']);

                    if ($is_series) {
                        $series_id = $event['EventSeries']['id'];
                        $count = count($identical_series_members[$series_id][$modified]);

                        // If events in a series have been modified, they are separated out
                        $count_series_parts = count($identical_series_members[$series_id]);
                        $series_part_event_ids = $identical_series_members[$series_id][$modified];
                    }
                ?>
                <li>
                    <ul class="actions">
                        <li>
                            <?php
                                $url = array('controller' => 'events', 'action' => 'approve');
                                if ($is_series) {
                                    $url = array_merge($url, $series_part_event_ids);
                                } else {
                                    $url[] = $event_id;
                                }
                                echo $this->Html->link(
                                    $this->Html->image('icons/tick.png').'Approve'.($published ? '' : ' and publish'),
                                    $url,
                                    array('escape' => false)
                                );
                            ?>
                        </li>
                        <li>
                            <?php
                                if ($is_series && $count > 1) {
                                    $confirm = 'You will only be editing this event, and not the '.($count - 1).' other '.__n('event', 'events', ($count - 1)).' in this series.';
                                } else {
                                    $confirm = false;
                                }
                                echo $this->Html->link(
                                    $this->Html->image('icons/pencil.png').'Edit',
                                    array(
                                        'controller' => 'events',
                                        'action' => 'edit',
                                        'id' => $event_id
                                    ),
                                    array('escape' => false, 'confirm' => $confirm)
                                );
                            ?>
                        </li>
                        <li>
                            <?php
                                $url = array('controller' => 'events', 'action' => 'delete');
                                if ($is_series && $count > 1) {
                                    $url = array_merge($url, $series_part_event_ids);
                                    if ($count_series_parts > 1) {
                                        $confirm = "All $count events in this part of the series will be deleted.";
                                    } else {
                                        $confirm = "All events in this series will be deleted.";
                                    }
                                    $confirm .= ' Are you sure?';
                                } else {
                                    $url[] = $event_id;
                                    $confirm = 'Are you sure?';
                                }
                                echo $this->Form->postLink(
                                    $this->Html->image('icons/cross.png').'Delete',
                                    $url,
                                    array('escape' => false, 'confirm' => $confirm),
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
                        <?php if ($is_series): ?>
                            <tr>
                                <th>
                                    Series
                                </th>
                                <td>
                                    <?php echo $event['EventSeries']['title']; ?>
                                    (<?php echo $count.__n(' event', ' events', $count); ?>)
                                    <?php if ($count_series_parts > 1 && $created != $modified): ?>
                                        <br />
                                        <strong>
                                            <?php echo __n('This event has', 'These events have', $count); ?>
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
                                <?php echo date('M j, Y g:ia', strtotime($created)); ?>
                                <?php if ($event['User']['id']): ?>
                                    by <?php echo $this->Html->link(
                                        $event['User']['name'],
                                        array('controller' => 'users', 'action' => 'view', 'id' => $event['User']['id'])
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
                                    <?php echo date('M j, Y g:ia', strtotime($modified)); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <th>
                                Date
                            </th>
                            <td>
                                <?php echo date('M j, Y', strtotime($event['Event']['date'])); ?>
                                <?php echo $this->Calendar->eventTime($event); ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                Category
                            </th>
                            <td>
                                <?php echo $event['Category']['name']; ?>
                            </td>
                        </tr>
                        <?php $vars_to_display = array('title', 'description', 'location', 'location_details', 'address', 'age_restriction', 'cost', 'source'); ?>
                        <?php foreach ($vars_to_display as $var): ?>
                            <?php if (!empty($event['Event'][$var])): ?>
                                <tr>
                                    <th>
                                        <?php echo Inflector::humanize($var); ?>
                                    </th>
                                    <td>
                                        <?php echo Sanitize::html($event['Event'][$var]); ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if (!empty($event['Tag'])): ?>
                            <tr>
                                <th>Tags</th>
                                <td>
                                    <?php
                                        $tags_list = array();
                                        foreach ($event['Tag'] as $tag) {
                                            $tags_list[] = $tag['name'];
                                        }
                                        echo implode(', ', $tags_list);
                                    ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($event['EventsImage'])): ?>
                            <tr>
                                <th>Images</th>
                                <td>
                                    <?php foreach ($event['EventsImage'] as $image): ?>
                                        <?php echo $this->Calendar->thumbnail('tiny', array(
                                            'filename' => $image['Image']['filename'],
                                            'caption' => $image['caption'],
                                            'group' => 'unapproved_event_'.$event['Event']['id']
                                        )); ?>
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
