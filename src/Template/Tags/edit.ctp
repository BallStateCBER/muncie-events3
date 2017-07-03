<?= $this->Form->create('Tag');?>
<?= $this->Form->control('id', [
    'default' => $this->request->data['id'],
    'type' => 'hidden'
]); ?>
<?= $this->Form->control('name', [
    'default' => $this->request->data['name']
]); ?>
<?= $this->Form->control('listed', [
    'default' => $this->request->data['listed'],
    'type' => 'checkbox',
    'label' => 'Listed?'
]); ?>
<div class="footnote">
    Unlisted tags are excluded from listed/suggested tags in event adding/editing forms.
</div>
<?= $this->Form->control('selectable', [
    'default' => $this->request->data['selectable'],
    'type' => 'checkbox',
    'label' => 'Selectable?'
]); ?>
<div class="footnote">
    Unselectable tags (generally group names, like "music genres") are excluded from auto-complete suggestions and are not selectable in event forms.
</div>
<?= $this->Form->control('parent_id', [
    'default' => $this->request->data['parent_id'],
    'type' => 'text',
    'label' => 'Parent ID'
]); ?>
<div class="footnote">
    Leave blank to place at the root level.
</div>
<?= $this->Form->submit('Update tag #'.$this->request->data['id']); ?>
<?= $this->Form->end(); ?>
