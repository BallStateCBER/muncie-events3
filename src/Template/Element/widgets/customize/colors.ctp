<?php
    $form_setup = [
        'Text colors' => [
            'Default' => 'textColorDefault',
            'Light' => 'textColorLight',
            'Link' => 'textColorLink'
        ],
        'Border colors' => [
            'Light' => 'borderColorLight',
            'Dark' => 'borderColorDark'
        ],
        'Background colors' => [
            'Default' => 'backgroundColorDefault',
            'Alt' => 'backgroundColorAlt'
        ]
    ];
?>
<?php foreach ($form_setup as $header => $fields): ?>
    <h3>
        <a href="#"><?= $header; ?></a>
    </h3>

    <div>
        <?php foreach ($fields as $label => $field_name): ?>
            <label for="Widget<?= $field_name; ?>">
                <?= $label; ?>:
            </label>
            <input id="Widget<?= $field_name; ?>" value="<?= $defaults['styles'][$field_name]; ?>" name="<?= $field_name; ?>" type="text" class="color_input style form-control" />
            <br />
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>
