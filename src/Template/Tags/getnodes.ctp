<?php
/**
 * @var \App\View\AppView $this
 */
    $data = [];
    foreach ($nodes as $node) {
        $text = ucwords($node->name).' ('.$node->id.')';
        if (! $node->selectable) {
            $text = '<span style="color: blue;">'.$text.'</span>';
        } elseif ($showNoEvents && isset($node->no_events) && $node->no_events) {
            $text = '<span style="color: red;">'.$text.'</span>';
        }
        $datum = [
            "text" => $text,
            "id" => $node->id,
            "cls" => "folder",
            "leaf" => ($node->lft + 1 == $node->rght)
        ];
        /* The 'Delete' group needs to be available to drag tags into,
         * but if it's emptied, it becomes a leaf. Here, that's prevented. */
        if (strtolower($node->name) == 'delete') {
            $datum['leaf'] = false;
        }
        if (isset($_GET['no_leaves'])) {
            $datum['leaf'] = false;
        }
        $data[] = $datum;
    }
    echo $this->Js->object($data);
