<?php
/**
 * @var \App\View\AppView $this
 */
?>
<fieldset class="col-md-5">
    <legend>Event Types</legend>
    <?php
        $formTemplate = [
            'inputContainer' => '{{content}}'
        ];
        $this->Form->setTemplates($formTemplate);
    ?>
    <div class="form-control mailing-options">
        <?= $this->Form->radio(
            'event_categories',
            [
                ['value' => 'all', 'text' => 'All Events'],
                ['value' => 'custom', 'text' => 'Custom']
            ],
            [
                'class' => 'category_options',
                'value' => 'all'
            ]
        ); ?>
    </div>
    <div id="custom_event_type_options">
        <?php if (isset($categories_error)): ?>
            <div class="error">
                <?= $categories_error; ?>
            </div>
        <?php endif; ?>
        <?php foreach ($categories as $key => $category): ?>
            <div class="form-control mailing-options">
                <?= $this->Icon->category($category) ?>
                <?= $this->Form->control(
                    'selected_categories.'.$key,
                    [
                        'type' => 'checkbox',
                        'label' => $category,
                        'hiddenField' => false,
                        'checked' => true
                    ]
                ); ?>
            </div>
        <?php endforeach; ?>
    </div>
</fieldset>
