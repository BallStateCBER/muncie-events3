<div class="mailing_list_settings">
    <?= $this->Form->create($mailingList, [
        'id' => 'MailingListForm'
        ]) ?>
    <fieldset>
        <h1 class="page_title">
            <?= $titleForLayout; ?>
        </h1>
        <div class="form-group col-lg-8 col-xs-12">
            <?= $this->Form->control('email', [
                'class' => 'form-control'
            ]); ?>
        </div>
        <div id="mailing_list_basic_options" class="form-group col-lg-8 col-xs-12">
            <div class="form-control mailing-options">
                <?= $this->Form->control('settings',
                    [
                        'type' => 'radio',
                        'options' => [
                            'default' => 'Default Settings',
                        ],
                        'default' => 'default',
                        'class' => 'settings_options',
                        'legend' => false,
                        'label' => false
                    ]
                ); ?>
            </div>
            <div class="form-control mailing-options">
                <?= $this->Form->control('settings',
                    [
                        'type' => 'radio',
                        'options' => [
                            'custom' => 'Custom'
                        ],
                        'default' => 'default',
                        'class' => 'settings_options',
                        'legend' => false,
                        'label' => false,
                    ]
                ); ?>
            </div>
        </div>
        <div id="custom_options" style="display: none;" class="row">
            <?= $this->element('mailing_list/frequency_options'); ?>
            <?= $this->element('mailing_list/category_options'); ?>
        </div>
    </fieldset>
    <?= $this->Form->button(__('Join Event Mailing List'), [
<<<<<<< HEAD
        'class' => 'btn btn-secondary']) ?>
=======
        'class' => 'btn btn-secondary btn-sm']) ?>
>>>>>>> 9c724c5a96b2752367e91b3c7e0ed7aa4a47a62e
    <?= $this->Form->end() ?>
</div>
<?php $this->Js->buffer("setupMailingListForm();"); ?>
