<?php

use Cake\Routing\Router;
use Cake\Utility\Inflector;

?>

<h1 class="page_title">
    Search Results
</h1>
<div id="search_results">
    <?php if (isset($term)): ?>
        <h2 class="search_results">
            <?php
                $total = $this->Paginator->counter([
                    'format' => '{:count}'
                ]);
                if (!$total) {
                    $total = 'No';
                }
                echo "$total ";
                if ($directionAdjective != 'all') {
                    echo "$directionAdjective ";
                }
                echo __n('event', 'events', $total)." containing \"$term\"";
                // Test
            ?>
        </h2>
    <?php endif; ?>

    <br />

    <?php
        if ($directionAdjective == 'all') {
            $breakdown = [];
            foreach ($counts as $dir => $count) {
                if ($count > 0) {
                    $url = array_merge($this->request->params['filter'], [
                        'direction' => ($dir == 'upcoming') ? 'future' : 'past'
                    ]);
                    $link_label = "$count $dir ".__n('event', 'events', $count);
                    $breakdown[] = $this->Html->link($link_label, $url);
                } else {
                    $breakdown[] = "no $dir events";
                }
            }
            echo ucfirst(implode(', ', $breakdown)).'.';
        } else {
            if ($oppositeEvents) {
                $url = Router::url([
                            'controller' => 'events',
                            'action' => 'search',
                            'filter' => $filter['filter'],
                            'direction' => ($direction == 'future') ? 'past' : 'future'
                        ], true);
                $link_label = $oppositeEvents.' matching ';
                $link_label .= (($direction == 'future') ? 'past ' : 'upcoming ');
                $link_label .= __n('event ', 'events ', $oppositeEvents);
                $link_label .= 'found';
                echo $this->Html->link($link_label, $url);
            } else {
                echo '<span class="text-muted">';
                echo 'No matching ';
                echo ($direction == 'future') ? 'past' : 'upcoming';
                echo ' events found.';
                echo '</span>';
            }
        }
    ?>

    <?php if (isset($tags) && ($tagCount)): ?>
        <div id="search_result_tags" class="alert alert-info">
            <p>
                Want to narrow your search?
                Some <?php echo $directionAdjective; ?> events have <?php echo __n('this', 'these', count($tags)); ?> matching <?php echo __n('tag', 'tags', count($tags)); ?>:
                <?php
                    $tagLinks = [];
                    foreach ($tags as $tag) {
                        $tagLinks[] = $this->Html->link($tag->name, [
                            'controller' => 'events',
                            'action' => 'tag',
                            'slug' => $tag->id.'_'.Inflector::slug($tag->name),
                            'direction' => $direction
                        ]);
                    }
                    echo $this->Text->toList($tagLinks);
                ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if (isset($events) && !empty($events)): ?>

        <?= $this->element('events/accordion_wrapper') ?>

        <?php $this->Js->buffer("setupEventAccordion();"); ?>

        <?= $this->element('pagination') ?>

    <?php elseif (!isset($this->request->getData('Event')['filter']) && empty($this->request->getData('Event')['filter'])): ?>
        <p class="alert alert-info">
            No upcoming events have been found.
        </p>
    <?php endif; ?>
</div>
