<?php
/**
 * @var \App\View\AppView $this
 */
?>
<?php if (!$this->request->getParam('isAjax')): ?>
    <h1 class="page_title">
        <?= $titleForLayout ?>
    </h1>
<?php endif; ?>

<?= $this->element('login') ?>
