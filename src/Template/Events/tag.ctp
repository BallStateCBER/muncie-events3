<h1 class="page_title">
    <?= $titleForLayout ?>
</h1>

<?php if (isset($events) && !empty($events)): ?>

    <?= $this->element('events/accordion_wrapper'); ?>

    <?php $this->Js->buffer("setupEventAccordion();"); ?>

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

<?php else: ?>
    <p class="alert alert-info">
        No events found.
    </p>
<?php endif; ?>
