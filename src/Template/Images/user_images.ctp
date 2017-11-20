<?php
use Cake\Core\Configure;

// Avoiding whitespace to prevent some display oddities
if (empty($images)) {
    echo 'No uploaded images to select.';
} else {
    $eventImgBaseUrl = Configure::read('App.eventImageBaseUrl');
    foreach ($images as $image_id => $filename) {
        echo '<a href="#" id="listed_image_'.$image_id.'" data-image-id="'.$image_id.'" data-image-filename="'.$filename.'">';
        echo '<img src="' . $eventImgBaseUrl . 'tiny/' . $filename . '" />';
        echo '</a>';
    }
}
