<?php $this->extend('default_wrapper'); ?>
<div id="content_wrapper" class="col-lg-9 col-md-8">
    <div id="content" class="clearfix">
        <?= $this->Flash->render('flash'); ?>
        <?= $this->fetch('content'); ?>
        <?php if ($this->request->getParam('controller') == 'Events' && isset($events) && ($this->request->action == 'index' || $this->request->action == 'category')): ?>
            <div id="event_accordion_loading_indicator" style="display: none;">
                <img id="" src="/img/loading_small.gif" /> Loading...
            </div>
            <div id="load_more_events_wrapper">
                <button class="btn" href="#" id="load_more_events">More events...</a>
            </div>
            <?php $this->Js->buffer("
                $('#load_more_events').button().click(function(event) {
                    event.preventDefault();
                    loadMoreEvents();
                });
            "); ?>
        <?php endif; ?>
    </div>
</div>
<?= $this->element('sidebar'); ?>
