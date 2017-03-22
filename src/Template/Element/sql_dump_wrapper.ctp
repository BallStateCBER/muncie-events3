<?php if (Configure::read('debug') != 0): ?>
	<br style="clear: both;" />
	<div class="sql_dump" style="clear: both;">
		<a href="#" id="sql_dump_toggler">Show SQL dump</a>
		<?php $this->Js->buffer("
			$('#sql_dump_toggler').click(function (event) {
				event.preventDefault();
				$('#sql_dump').toggle();
			});
		"); ?>
	</div>
	<div id="sql_dump" style="clear: both; display: none;">
		<a name="sql_dump"></a>
		<?php echo $this->element('sql_dump'); ?>
	</div>
<?php endif; ?>