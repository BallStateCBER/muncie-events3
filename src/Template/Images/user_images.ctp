<?php
use Cake\Core\Configure;

// Avoiding whitespace to prevent some display oddities
if (empty($images)) {
    echo 'No uploaded images to select.';
} else {
    $eventImgBaseUrl = Configure::read('App.eventImageBaseUrl');
    foreach ($images as $image) {
        echo '<a href="#" id="listed_image_'.$image['id'].'" data-image-id="'.$image['id'].'" data-image-filename="'.$image['filename'].'">';
        echo '<img src="' . $eventImgBaseUrl . 'tiny/' . $image['filename'] . '" />';
        echo '</a>';
    }
}
