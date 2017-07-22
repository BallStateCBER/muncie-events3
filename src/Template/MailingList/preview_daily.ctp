<?php if (empty($events)): ?>
	<h1 class="page_title">
		Daily Email Preview Unavailable
	</h1>
	<p class="notification_message">
		Can't show a preview, because there are no events that
		match the parameters of this particular email.
	</p>
<?php else: ?>
	<h1 class="page_title">
		Daily Email Preview
	</h1>
	<fieldset>
		<legend>HTML</legend>
		<?php echo $this->element('../Emails/html/daily'); ?>
	</fieldset>
	
	<fieldset>
		<legend>Plain</legend>
		<pre style="width: 600px; overflow: auto;"><?php echo $this->element('../Emails/text/daily'); ?></pre>
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
