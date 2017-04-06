<?php
    $text_colors = [
        'Default' => 'textColorDefault',
        'Light' => 'textColorLight',
        'Link' => 'textColorLink'
    ];
?>
<?php foreach ($text_colors as $label => $field_name): ?>
    <label for="Widget<?php echo $field_name; ?>">
        <?php echo $label; ?> color:
    </label>
    <input id="Widget<?php echo $field_name; ?>" value="<?php echo $defaults['styles'][$field_name]; ?>" name="<?php echo $field_name; ?>" type="text" class="color_input style" />
    <br />
<?php endforeach; ?>
