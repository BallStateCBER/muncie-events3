<?php
/**
 * @var \App\View\AppView $this
 */
?>
<h1 class="page_title">
	<?= $titleForLayout; ?>
</h1>

<p>
	This form will bulk-add email addresses to the event mailing list with the default settings
	(weekly, all categories). If there is an error with an email address, it will remain in
	the text box below after submitting the form. Otherwise, addresses successfully added
	will be removed from the text box.
</p>

<?= $this->Form->create(false); ?>
<?= $this->Form->input('email_addresses', ['type' => 'textarea']); ?>
<?= $this->Form->submit(__('Bulk Add'), [
    'class' => 'btn btn-secondary btn-sm'
    ]) ?>
<?= $this->Form->end(); ?>
