<?php
/**
 * @var \App\View\AppView $this
 */
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US" xmlns:fb="https://www.facebook.com/2008/fbml">
<head>
    <link rel="dns-prefetch" href="//ajax.googleapis.com" />
    <?= $this->Html->charset(); ?>
    <title>
        Muncie Events
    </title>
    <?php
        echo $this->fetch('meta');
        echo $this->Html->css('/magnific-popup/magnific-popup.css');
        echo $this->Html->css('widgets/month');
        echo $this->fetch('css');
        echo $this->Html->css('https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css');
        echo $this->Html->script('widgets/month');
    ?>
    <?php if (!empty($customStyles)): ?>
        <style>
            <?php foreach ($customStyles as $element => $rules): ?>
                <?= $element; ?> {<?= implode('', $rules); ?>}
            <?php endforeach; ?>
        </style>
    <?php endif; ?>
    <base target="_top" />
</head>
<body>
    <div class="header">
        <?= $this->element('widgets/header'); ?>
    </div>
    <div id="calendar_container">
        <?= $this->fetch('content'); ?>
    </div>
    <div id="event_lists" style="display: none;"></div>
    <div id="events"></div>
    <div id="loading" style="display: none;">
        <div></div>
        <div></div>
    </div>
    <?php $this->Js->buffer("muncieEventsMonthWidget.prepareWidget();"); ?>
    <noscript>
        <div id="noscript">
            JavaScript is currently disabled in your browser.
            To use this calendar, JavaScript must be enabled.
            If you need assistance, <a href="http://www.enable-javascript.com/" target="_blank">Enable-JavaScript.com</a> provides instructions.
        </div>
    </noscript>
    <?php
        /*
        // Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline ?>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="/data_center/js/jquery-1.8.3.min.js"><\/script>')</script>
        */
    ?>
    <script src="/js/jquery-1.9.0.min.js"></script>
    <script src="/js/script.js"></script>
    <script type="text/javascript" src="/magnific-popup/jquery.magnific-popup.min.js"></script>
    <script type="text/javascript" src="/js/image_popups.js"></script>
    <?php
        $this->Js->buffer("muncieEventsImagePopups.prepare();");
        echo $this->fetch('script');
        echo $this->Js->writeBuffer();
        echo $this->element('analytics');
        #echo $this->Facebook->init();
    ?>
</body>
</html>
