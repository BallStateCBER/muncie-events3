<?php
/**
 * @var \App\View\AppView $this
 */
?>
<?php $this->extend('default_wrapper'); ?>

<div id="content_wrapper" class="col-sm-12 col-lg-12">
	<div id="content" class="clearfix">
		<?php echo $this->fetch('content'); ?>
	</div>
</div>
