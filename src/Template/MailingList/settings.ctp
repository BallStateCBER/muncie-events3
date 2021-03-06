<?php
/**
 * @var \App\View\AppView $this
 */
?>
<?php if (isset($recipientId) && isset($hash)): ?>
    <?php
    $form_url = ['controller' => 'mailing_list', 'action' => 'settings'];
    if ($recipientId && $hash) {
        $form_url[] = $recipientId;
        $form_url[] = $hash;
    }
    ?>

    <div id="mailing_list_settings">
        <h1 class="page_title">
            <?= $titleForLayout ?>
        </h1>

        <?= $this->Form->create('MailingList', [
            'url' => $form_url,
            'id' => 'MailingListForm'
        ]);?>

        <fieldset>
            <legend>Email Address</legend>
            <?= $this->Form->control('email', [
                'label' => 'Email',
                'value' => $recipient['email'],
                'class' => 'form-control'
            ]) ?>
        </fieldset>

        <?= $this->element('mailing_list/frequency_options') ?>
        <?= $this->element('mailing_list/category_options') ?>

        <fieldset>
            <legend>Unsubscribe</legend>
            <?= $this->Form->control(
                'unsubscribe',
                [
                    'type' => 'checkbox',
                    'label' => 'Remove me from the mailing list'
                ]
            ) ?>
        </fieldset>

        <?= $this->Form->submit('Update', [
            'class' => 'btn btn-secondary btn-sm',
        ]) ?>
    </div>
<?php endif ?>