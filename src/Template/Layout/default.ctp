<?php $this->extend('default_wrapper'); ?>
<div id="content_wrapper" class="col-md-9">
    <div id="content" class="clearfix">
        <?= $this->Flash->render('flash'); ?>
        <?= $this->fetch('content'); ?>
    </div>
</div>
<?= $this->element('sidebar'); ?>
