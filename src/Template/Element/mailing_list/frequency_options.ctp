<fieldset class="col-md-6">
	<legend>Frequency</legend>
	<?php echo $this->Form->input(
		'frequency',
		array(
			'type' => 'radio',
			'options' => array(
				'weekly' => 'Weekly <span class="text-muted">(Thursday, next week\'s events)</span>'
			),
			'class' => 'frequency_options',
			'div' => array(
				'class'=>'form-control mailing-options'
			),
			'legend' => false
		)
	); ?>
	<?php echo $this->Form->input(
		'frequency',
		array(
			'type' => 'radio',
			'options' => array(
				'daily' => 'Daily <span class="text-muted">(Every morning, today\'s events)</span>'
			),
			'class' => 'frequency_options',
			'div' => array(
				'class'=>'form-control mailing-options'
			),
			'legend' => false
		)
	); ?>
	<?php echo $this->Form->input(
		'frequency',
		array(
			'type' => 'radio',
			'options' => array(
				'custom' => 'Custom'
			),
			'class' => 'frequency_options',
			'div' => array(
				'class'=>'form-control mailing-options'
			),
			'legend' => false
		)
	); ?>
	<div id="custom_frequency_options">
		<?php if (isset($frequency_error)): ?>
			<div class="error">
				<?php echo $frequency_error; ?>
			</div>
		<?php endif; ?>
		<table>
			<tr>
				<th>
					Weekly:
				</th>
				<td>
					<?php echo $this->Form->input(
						'weekly',
						array(
							'type' => 'checkbox',
							'label' => ' Thursday',
							'div' => false
						)
					); ?>
				</td>
			</tr>
			<tr>
				<th>
					Daily:
				</th>
				<td>
					<?php foreach ($days as $code => $day): ?>
						<?php echo $this->Form->input(
							"daily_$code",
							array(
								'type' => 'checkbox',
								'label' => false,
								'div' => false,
								'id' => 'daily_'.$code
							)
						); ?>
						<label for="daily_<?php echo $code; ?>">
							<?php echo $day; ?>
						</label>
						<br />
					<?php endforeach; ?>
				</td>
			</tr>
		</table>
	</div>
</fieldset>
