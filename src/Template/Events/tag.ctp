<h1 class="page_title">
    <?= $titleForLayout ?>
</h1>

<?php if (isset($events) && !empty($events)): ?>

    <?= $this->element('events/accordion_wrapper'); ?>

    <?php $this->Js->buffer("setupEventAccordion();"); ?>

    <?= $this->element('pagination') ?>

<?php else: ?>
    <p class="alert alert-info">
        No events found.
    </p>
<?php endif; ?>
