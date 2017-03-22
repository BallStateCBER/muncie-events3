<?php
	$form_setup = array(
		'Text colors' => array(
			'Default' => 'textColorDefault',
			'Light' => 'textColorLight',
			'Link' => 'textColorLink'
		),
		'Border colors' => array(
			'Light' => 'borderColorLight',
			'Dark' => 'borderColorDark'
		),
		'Background colors' => array(
			'Default' => 'backgroundColorDefault',
			'Alt' => 'backgroundColorAlt'
		)
	);
?>
<?php foreach ($form_setup as $header => $fields): ?>
	<h3>
		<a href="#"><?php echo $header; ?></a>
	</h3>
	
	<div>
		<?php foreach ($fields as $label => $field_name): ?>
			<label for="Widget<?php echo $field_name; ?>">
				<?php echo $label; ?>:
			</label>
			<input id="Widget<?php echo $field_name; ?>" value="<?php echo $defaults['styles'][$field_name]; ?>" name="<?php echo $field_name; ?>" type="text" class="color_input style" />
			<br />
		<?php endforeach; ?>
	</div>
<?php endforeach; ?>