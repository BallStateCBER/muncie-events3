<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div id="front_page_announcement" class="alert alert-info">
    <h2>What is Muncie Events?</h2>
    <p>
        A free service that distributes event information for Muncie, IN through a
        <?php echo $this->Html->link('network of online calendars', [
            'controller' => 'widgets',
            'action' => 'index'
        ]); ?>
        and a personalized
        <?php echo $this->Html->link('mailing list', [
            'controller' => 'mailing_list',
            'action' => 'join'
        ]); ?>.
        We're constantly developing this project to meet the needs of our community,
        so stay tuned for more bells and whistles.
        If you like what we're doing, please
        <a href="http://facebook.com/MuncieEvents">like us on Facebook</a>,
        <a href="/contact">give us your feedback</a>, and
        <a href="/about">read more</a> about the project.
    </p>
</div>
