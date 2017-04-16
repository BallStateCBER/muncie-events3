<?php
// Avoiding whitespace to prevent some display oddities
if (empty($images)) {
    echo 'No uploaded images to select.';
} else {
    foreach ($images as $image_id => $filename) {
        echo '<a href="#" id="listed_image_'.$image_id.'" data-image-id="'.$image_id.'" data-image-filename="'.$filename.'">';
        echo '<img src="/img/events/tiny/'.$filename.'" />';
        echo '</a>';
    }
}
