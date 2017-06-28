<?php
use Cake\Routing\Router;

$url = Router::url([
        'controller' => 'events',
        'action' => 'view',
        'id' => $event->id
    ], true);
?>
<li <?= (!empty($event->images)) ? 'class="with_images"' : ''; ?>>
    <?php if (!empty($event->images)): ?>
        <span class="tiny_thumbnails">
            <?php
                foreach ($event->images as $image) {
                    echo $this->Calendar->thumbnail('tiny', [
                        'filename' => $image->filename,
                        'caption' => $image->caption,
                        'group' => 'event'.$event->id.'_tiny_tn'
                    ]);
                }
            ?>
        </span>
    <?php endif; ?>
    <a data-toggle="collapse" data-target="#more_info_<?= $event->id; ?>" href="<?= $url; ?>" title="Click for more info" class="more_info_handle" id="more_info_handle_<?= $event->id; ?>" data-event-id="<?= $event->id; ?>">
        <?= $this->Icon->category($event->category->name); ?>
        <span class="title">
            <?= $event->title; ?>
        </span>
        <span class="when">
            <?= $this->Calendar->eventTime($event); ?>
            @
        </span>
        <span class="where">
            <?= $event->location ? $event->location : '&nbsp;'; ?>
            <?php if ($event->location_details): ?>
                <span class="location_details">
                    <?= $event->location_details; ?>
                </span>
            <?php endif; ?>
            <?php if ($event->address): ?>
                <span class="address">
                     <?= $event->address; ?>
                </span>
            <?php endif; ?>
        </span>
    </a>

    <div class="collapse" id="more_info_<?= $event->id; ?>">
    <div class="card">
        <div class="card-header">
            <?= $this->element('events/actions', compact('event')); ?>
        </div>
        <div class="description">
            <?php if (!empty($event->images)): ?>
                <div class="images">
                    <?php foreach ($event->images as $image): ?>
                        <?= $this->Calendar->thumbnail('small', [
                            'filename' => $image['filename'],
                            'caption' => $image->caption,
                            'group' => 'event'.$event->id
                        ]); ?>
                        <?php if ($image->caption): ?>
                            <span class="caption">
                                <?= $image->caption; ?>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($event->description): ?>
                <?= $this->Text->autolink($event->description, ['escape' => false]); ?>
            <?php endif; ?>
            <?php if ($event->cost || $event->age_restriction): ?>
                <div class="details">
                    <table>
                        <?php if ($event->cost): ?>
                        <tr class="cost">
                            <th>Cost:</th>
                            <td>
                                <?= $event->cost; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($event->age_restriction): ?>
                        <tr class="age_restriction detail" id="age_restriction_<?= $event->id; ?>">
                            <th>Ages:</th>
                            <td>
                                <?= $event->age_restriction; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <table class="details">
                <?php if (!empty($event->tags)): ?>
                <tr class="tags">
                    <th>Tags:</th>
                    <td>
                        <?= $this->Calendar->eventTags($event); ?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($event->series_id) && !empty($event->event_series->title)): ?>
                <tr class="tags">
                    <th>Series:</th>
                    <td>
                        <?= $this->Html->link($event->event_series->title, [
                                    'controller' => 'event_series',
                                    'action' => 'view', $event->series_id
                                ]); ?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php if ($event->source): ?>
                <tr class="source">
                    <th>Source:</th>
                    <td>
                        <?= $this->Text->autoLink($event->source); ?>
                    </td>
                </tr>
                <?php endif; ?>
                <tr class="link">
                    <th>Link:</th>
                    <td>
                        <?= $this->Html->link($url, $url); ?>
                    </td>
                </tr>
                <?php if (isset($event->user->name) && $event->user->name): ?>
                <tr class="author">
                    <th>
                        Author:
                    </th>
                    <td>
                        <?= $this->Html->link($event->user->name,
                                    ['controller' => 'users', 'action' => 'view', 'id' => $event->user->id]
                                 ); ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        </div>
        </div>
</li>
