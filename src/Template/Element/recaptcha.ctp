<?php $recaptcha_error = $this->Recaptcha->error(); ?>
<div class="input required recaptcha <?php if ($recaptcha_error) {
    echo 'error';
} ?>">
	<?php if (! isset($label)) {
    $label = 'Spam Protection';
} ?>
	<?php if ($label !== false): ?>
		<label for="recaptcha_response_field">
			<?php echo $label; ?>
		</label>
	<?php endif; ?>
	<?php echo $this->Recaptcha->show(array('theme' => 'clean')); ?>
	<div class="footnote">
		By typing these two words and verifying that you're not a spam-bot, you're helping digitize old books and newspapers.
	</div>
	<?php echo $recaptcha_error; ?>
</div>
