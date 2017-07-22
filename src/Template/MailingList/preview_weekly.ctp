<?php if (empty($events)): ?>
	<h1 class="page_title">
		Weekly Email Preview Unavailable
	</h1>
	<p class="alert alert-info">
		Can't show a preview, because there are no events that
		match the parameters of this particular email.
	</p>
<?php else: ?>
	<h1 class="page_title">
		Weekly Email Preview
	</h1>

	<?php if (! $is_delivery_day): ?>
		<p class="alert alert-info">
			Note that today is not the day designated for delivering weekly emails.
		</p>
	<?php endif; ?>

	<fieldset>
		<legend>HTML</legend>
		<?php echo $this->element('../Emails/html/weekly'); ?>
	</fieldset>

	<fieldset>
		<legend>Plain</legend>
		<pre style="width: 600px; overflow: auto;"><?php echo $this->element('../Emails/text/weekly'); ?></pre>
	</fieldset>

	<fieldset>
		<legend>Recipients</legend>
		<?php foreach ($recipient_results as $result): ?>
			<p class="<?php echo $result['class']; ?>_message">
				<?php echo $result['message']; ?>
			</p>
		<?php endforeach; ?>
	</fieldset>
<?php endif; ?>
