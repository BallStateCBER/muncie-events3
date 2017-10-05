<div class="paginator">
    <ul class="pagination">
        <?= $this->Paginator->first('<< ' . __('first', ['class' => 'page-link'])) ?>
        <?= $this->Paginator->hasPrev() ? $this->Paginator->prev('< ' . __('previous', ['class' => 'page-link'])) : '' ?>
        <?= $this->Paginator->numbers() ?>
        <?= $this->Paginator->hasNext() ? $this->Paginator->next(__('next') . ' >', ['class' => 'page-link']) : '' ?>
        <?= $this->Paginator->last(__('last') . ' >>', ['class' => 'page-link']) ?>
    </ul>
    <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
</div>
