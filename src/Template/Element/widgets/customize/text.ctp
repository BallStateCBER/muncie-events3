<?php
    $text_colors = [
        'Default' => 'textColorDefault',
        'Light' => 'textColorLight',
        'Link' => 'textColorLink'
    ];
?>
<?php foreach ($text_colors as $label => $field_name): ?>
    <label for="Widget<?= $field_name; ?>">
        <?= $label; ?> color:
    </label>
    <input id="Widget<?= $field_name; ?>" value="<?= $defaults['styles'][$field_name]; ?>" name="<?= $field_name; ?>" type="text" class="color_input style" />
    <br />
<?php endforeach; ?>
