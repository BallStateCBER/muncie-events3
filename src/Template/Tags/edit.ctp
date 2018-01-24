<?= $this->Form->create('Tag');?>
<?= $this->Form->control('id', [
    'default' => $this->request->getData('id'),
    'type' => 'hidden'
]); ?>
<?= $this->Form->control('name', [
    'class' => 'form-control',
    'default' => $this->request->getData('name')
]); ?>
<?= $this->Form->control('listed', [
    'class' => 'form-control',
    'default' => $this->request->getData('listed'),
    'type' => 'checkbox',
    'label' => 'Listed?'
]); ?>
    <div class="footnote">
        Unlisted tags are excluded from listed/suggested tags in event adding/editing forms.
    </div>
<?= $this->Form->control('selectable', [
    'class' => 'form-control',
    'default' => $this->request->getData('selectable'),
    'type' => 'checkbox',
    'label' => 'Selectable?'
]); ?>
    <div class="footnote">
        Unselectable tags (generally group names, like "music genres") are excluded from auto-complete suggestions and are not selectable in event forms.
    </div>
<?= $this->Form->control('parent_id', [
    'class' => 'form-control',
    'default' => $this->request->getData('parent_id'),
    'type' => 'text',
    'label' => 'Parent ID'
]); ?>
    <div class="footnote">
        Leave blank to place at the root level.
    </div>
<?= $this->Form->submit('Update tag #' . $this->request->getData('id'), ['class' => 'btn btn-default']); ?>
<?= $this->Form->end(); ?>