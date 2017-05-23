<fieldset class="col-md-5">
    <legend>Event Types</legend>
    <?php
        $formTemplate = [
            'inputContainer' => '{{content}}'
        ];
        $this->Form->setTemplates($formTemplate);
    ?>
    <div class="form-control mailing-options">
        <?= $this->Form->input(
            'event_categories',
            [
                'type' => 'radio',
                'options' => [
                    'all' => 'All Events'
                ],
                'class' => 'category_options',
                'legend' => false,
                'label' => false
            ]
        ); ?>
    </div>
    <div class="form-control mailing-options">
        <?= $this->Form->input(
            'event_categories',
            [
                'type' => 'radio',
                'options' => [
                    'custom' => 'Custom'
                ],
                'class' => 'category_options',
                'legend' => false,
                'label' => false
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
                <?= $this->Form->input(
                    'MailingList.selected_categories.'.$key,
                    [
                        'type' => 'checkbox',
                        'label' => $category,
                        'hiddenField' => false
                    ]
                ); ?>
            </div>
        <?php endforeach; ?>
    </div>
</fieldset>
