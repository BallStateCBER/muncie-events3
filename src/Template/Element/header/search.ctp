<div class="dropdown">
	<?php
		echo $this->Form->create('Event', array(
			'id' => 'EventSearchForm',
			# 'url' => array_merge(array('action' => 'search'), $this->request->params['pass'])
		));
		echo $this->Form->input('filter', array(
			'div' => false,
			'label' => false,
			'class' => 'form-control',
			'before' => '<img src="/img/loading_small_dark.gif" id="search_autocomplete_loading" />',
			'after' => $this->Form->submit(__('Search'), array('div' => false, 'class' => 'btn btn-secondary btn-sm')).'',
		));
	?>
	<button id="search_options_toggler" class="dropdown-toggle btn btn-secondary btn-sm" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Advanced</button>
	<div id="search_options" class="dropdown-menu" aria-labelledby="search_options_toggler">
		<div>
			<?php
				echo $this->Form->input('direction', array(
					'options' => array(
						'future' => 'Upcoming Events',
						'past' => 'Past Events',
						'all' => 'All Events'
					),
					'default' => 'future',
					'type' => 'radio',
					'legend' => false,
					'separator' => '<br />'
				));
			?>
		</div>
	</div>
	<?php echo $this->Form->end(); ?>
	<?php $this->Js->buffer("setupSearch();"); ?>
</div>
