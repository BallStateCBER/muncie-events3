<?php $this->extend('default_wrapper'); ?>

<div id="content_wrapper" class="col-md-9">
	<div id="content" class="clearfix">
		<?php echo $this->fetch('content'); ?>
	</div>
</div>

<?php echo $this->element('sidebar'); ?>
