<fieldset class="col-md-6">
    <legend>Event Types</legend>

    <?php echo $this->Form->input(
        'event_categories',
        [
            'type' => 'radio',
            'options' => [
                'all' => 'All Events'
            ],
            'class' => 'category_options',
            'div' => [
                'class'=>'form-control mailing-options'
            ],
            'legend' => false
        ]
    ); ?>
    <?php echo $this->Form->input(
        'event_categories',
        [
            'type' => 'radio',
            'options' => [
                'custom' => 'Custom'
            ],
            'class' => 'category_options',
            'div' => [
                'class'=>'form-control mailing-options'
            ],
            'legend' => false
        ]
    ); ?>
    <div id="custom_event_type_options">
        <?php if (isset($categories_error)): ?>
            <div class="error">
                <?php echo $categories_error; ?>
            </div>
        <?php endif; ?>
        <?php foreach ($categories as $category): ?>
            <?php echo $this->Form->input(
                'MailingList.selected_categories.'.$category['Category']['id'],
                [
                    'type' => 'checkbox',
                    'label' => $this->Icon->category($category['Category']['name']).' '.$category['Category']['name'],
                    'hiddenField' => false
                ]
            ); ?>
        <?php endforeach; ?>
    </div>
</fieldset>
