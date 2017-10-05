<h1><?= $location ?></h1>
<?php echo $this->element('events/accordion_wrapper'); ?>
<div class="paginator">
    <ul class="pagination">
        <?= $this->Paginator->first('<< ' . __('first')) ?>
        <?= $this->Paginator->hasPrev() ? $this->Paginator->prev('< ' . __('previous')) : '' ?>
        <?= $this->Paginator->numbers() ?>
        <?= $this->Paginator->hasNext() ? $this->Paginator->next(__('next') . ' >') : '' ?>
        <?= $this->Paginator->last(__('last') . ' >>') ?>
    </ul>
    <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
</div>
