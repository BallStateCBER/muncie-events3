<?php
    $border_colors = [
        'Light' => 'borderColorLight',
        'Dark' => 'borderColorDark'
    ];
?>
<?php foreach ($border_colors as $label => $field_name): ?>
    <label for="Widget<?= $field_name; ?>">
        <?= $label; ?> color:
    </label>
    <input id="Widget<?= $field_name; ?>" value="<?= $defaults['styles'][$field_name]; ?>" name="<?= $field_name; ?>" type="text" class="color_input style form-control" />
    <br />
<?php endforeach; ?>
