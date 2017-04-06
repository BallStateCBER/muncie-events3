<h1 class="page_title">
    <?php echo $titleForLayout; ?>
</h1>

<div class="alert alert-danger">
    <p>
        Sorry, your event appears to be spam because its title includes the word <strong>"<?php echo $spammy_word ?>"</strong>.
    </p>
    <p>
        Please <a href="javascript:history.go(-1)">go back</a> to correct your event's information
        or
        <?php echo $this->Html->link(
            'contact an administrator',
            [
                'controller' => 'pages',
                'action' => 'contact'
            ]
        ); ?>
        for assistance.
    </p>
</div>
