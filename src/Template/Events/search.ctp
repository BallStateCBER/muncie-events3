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
                if ($direction_adjective != 'all') {
                    echo "$direction_adjective ";
                }
                echo __n('event', 'events', $total)." containing \"$term\"";
                // Test
            ?>
        </h2>
    <?php endif; ?>

    <br />

    <?php
        if ($direction_adjective == 'all') {
            $breakdown = [];
            foreach ($counts as $dir => $count) {
                if ($count > 0) {
                    $url = array_merge($this->request->params['named'], [
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
            if ($events_found_in_other_direction) {
                $url = array_merge($this->request->params['named'], [
                    'direction' => ($direction == 'future') ? 'past' : 'future'
                ]);
                $link_label = $events_found_in_other_direction.' matching ';
                $link_label .= (($direction == 'future') ? 'past ' : 'upcoming ');
                $link_label .= __n('event ', 'events ', $events_found_in_other_direction);
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

    <?php if (isset($tags) && !empty($tags)): ?>
        <div id="search_result_tags" class="alert alert-info">
            <p>
                Want to narrow your search?
                Some <?php echo $direction_adjective; ?> events have <?php echo __n('this', 'these', count($tags)); ?> matching <?php echo __n('tag', 'tags', count($tags)); ?>:
                <?php
                    $tag_links = [];
                    foreach ($tags as $tag) {
                        $tag_links[] = $this->Html->link($tag['Tag']['name'], [
                            'controller' => 'events',
                            'action' => 'tag',
                            'slug' => $tag['Tag']['id'].'_'.Inflector::slug($tag['Tag']['name']),
                            'direction' => $direction
                        ]);
                    }
                    echo $this->Text->toList($tag_links);
                ?>
            </p>
        </div>
    <?php endif; ?>

    <?php if (isset($events) && !empty($events)): ?>

        <?php echo $this->element('pagination'); ?>

        <?php foreach ($events as $date => $days_events): ?>
            <?php echo $this->Calendar->dayHeaders($date); ?>
            <?php echo $this->element('events/accordion_day', [
                'events' => $days_events
            ]); ?>
        <?php endforeach; ?>

        <?php echo $this->element('pagination'); ?>

        <?php $this->Js->buffer("setupEventAccordion();"); ?>

    <?php elseif (!isset($this->request->data['Event']['filter']) && empty($this->request->data['Event']['filter'])): ?>
        <p class="alert alert-info">
            Please enter a word or phrase in the search box to search for events.
        </p>
    <?php endif; ?>
</div>
