<h1 class="page_title">
	<?php echo $category['Category']['name']; ?>
	<?php echo $this->Icon->category($category['Category']['name']); ?>
</h1>

<?php
	$this->Js->buffer("
		muncieEvents.requestEventFilters.category = {$category['Category']['id']};
	"); 
	echo $this->element('events/accordion_wrapper');
?>