<h1 class="page_title">Event Feed</h1>

<?php echo $this->Html->link(
	'&larr; Back to Widgets Overview',
	array('action' => 'index'),
	array('escape' => false, 'class' => 'under_header_back')
); ?>

<div class="widget_controls_wrapper">
	<div class="widget_controls">
		<h2>Customize Your Widget</h2>
		<form>
			<h3>
				<a href="#">Events</a>
			</h3>
			<div id="WidgetFilterOptions">
				<?php echo $this->element('widgets/customize/events'); ?>
			</div>

			<h3>
				<a href="#">Text</a>
			</h3>
			<div class="text">
				<?php echo $this->element('widgets/customize/text'); ?>
			</div>

			<h3>
				<a href="#">Borders</a>
			</h3>
			<div class="borders">
				<?php echo $this->element('widgets/customize/borders'); ?>
				<input type="checkbox" name="outerBorder" checked="checked" value="1" class="option" /> Border around widget
			</div>

			<h3>
				<a href="#">Backgrounds</a>
			</h3>
			<div class="backgrounds">
				<?php echo $this->element('widgets/customize/backgrounds'); ?>
			</div>

			<h3>
				<a href="#">Size</a>
			</h3>
			<div>
				<label for="WidgetHeight">
					Height:
				</label>
				<input id="WidgetHeight" value="<?php echo $defaults['iframe_options']['height']; ?>px" name="height" type="text" class="style" />

				<br />

				<label for="WidgetWidth">
					Width:
				</label>
				<input id="WidgetWidth" value="<?php echo $defaults['iframe_options']['width']; ?>px" name="width" type="text" class="style" />
				<p class="text-muted">
					Sizes can be in pixels (e.g. 300px) or percentages (e.g. 100%).
					A <strong>minimum width</strong> of 200px is recommended.
				</p>
			</div>

			<br />
			<input type="submit" value="Apply changes" />
		</form>
	</div>
	<div class="widget_demo" id="widget_demo"></div>
	<br class="clear" />
</div>

<?php
	$this->Html->script('/jPicker/jpicker-1.1.6.js', array('inline' => false));
	$this->Html->css('/jPicker/css/jpicker-1.1.6.min.css', null, array('inline' => false));
	$this->Html->css('/jPicker/jPicker.css', null, array('inline' => false));
	$this->Html->script('widgets/customize.js', array('inline' => false));
	$this->Js->buffer("widgetCustomizer.setupWidgetDemo('feed');");
?>
