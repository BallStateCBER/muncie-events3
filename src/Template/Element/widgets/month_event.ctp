<?php
echo "<li>";

    // Event link
    $link_text = $this->Text->truncate(
        $event->title,
        50,
        [
            'ending' => '...',
            'exact' => false
        ]
    );
$category_name = $event->category->name;
$link_text = $this->Icon->category($category_name).$link_text;
echo $this->Html->link(
        $link_text,
        [
            'controller' => 'events',
            'action' => 'view',
            'id' => $event->id
        ],
        [
            'escape' => false,
            'class' => 'event',
            'data-event-id' => $event->id,
            'title' => $event->displayed_time.' - '.$event->title
        ]
    );

echo '</li>';
