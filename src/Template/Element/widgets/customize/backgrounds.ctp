<?php
    $background_colors = [
        'Default' => 'backgroundColorDefault',
        'Alt' => 'backgroundColorAlt'
    ];
?>
<?php foreach ($background_colors as $label => $field_name): ?>
    <label for="Widget<?= $field_name; ?>">
        <?= $label; ?> color:
    </label>
    <input id="Widget<?= $field_name; ?>" value="<?= $defaults['styles'][$field_name]; ?>" name="<?= $field_name; ?>" type="text" class="color_input style" />
    <br />
<?php endforeach; ?>
