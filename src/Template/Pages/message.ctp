<?php if (isset($titleForLayout)): ?>
    <h1 class="page_title">
        <?php echo $titleForLayout; ?>
    </h1>
<?php endif; ?>

<?php if (isset($message) && $message): ?>
    <p class="<?php if (isset($class) && $class) {
    echo $class.'_message';
} ?>">
        <?php echo $message; ?>
    </p>
<?php endif; ?>

<?php if (isset($back) && $back): ?>
    <?php echo $this->Html->link('&larr; Back', $back, ['escape' => false]); ?>
<?php endif; ?>
