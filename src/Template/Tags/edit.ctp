<?php echo $this->Form->create('Tag');?>
<?php echo $this->Form->input('id', array('type' => 'hidden')); ?>
<?php echo $this->Form->input('name'); ?>
<?php echo $this->Form->input('listed', array(
	'after' => '<div class="footnote">Unlisted tags are excluded from listed/suggested tags in event adding/editing forms.</div>'
)); ?>
<?php echo $this->Form->input('selectable', array(
	'after' => '<div class="footnote">Unselectable tags (generally group names, like "music genres") are excluded from auto-complete suggestions and are not selectable in event forms.</div>'
)); ?>
<?php echo $this->Form->input('Tag.parent_id', array(
	'type' => 'text',
	'label' => 'Parent ID',
	'after' => '<div class="footnote">Leave blank to place at the root level.</div>'
)); ?>
<?php echo $this->Form->end('Update tag #'.$this->request->data['Tag']['id']); ?>