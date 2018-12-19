<?php
/**
 * @var \App\View\AppView $this
 */
?>
<?php if (!$this->request->params['isAjax']): ?>
    <h1 class="page_title">
        <?php echo $titleForLayout; ?>
    </h1>
<?php endif; ?>

<?php echo $this->element('login'); ?>
