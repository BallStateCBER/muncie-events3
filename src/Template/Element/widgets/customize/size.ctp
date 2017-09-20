<div>
	<label for="WidgetHeight">
		Height:
	</label>
	<input id="WidgetHeight" value="<?= $defaults['iframeOptions']['height']; ?>px" name="height" type="text" class="style form-control" />

	<br />

	<label for="WidgetWidth">
		Width:
	</label>
	<input id="WidgetWidth" value="<?= $defaults['iframeOptions']['width']; ?>px" name="width" type="text" class="style form-control" />
	<p class="text-muted">
		Sizes can be in pixels (e.g. 300px) or percentages (e.g. 100%).
	</p>
</div>
