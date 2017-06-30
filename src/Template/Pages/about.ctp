<h1 class="page_title">
    About Muncie Events
</h1>

<p>
    Muncie Events is a comprehensive event promotion tool provided to the city of
    Muncie, Indiana by the Center for Business and Economic Research at Ball State
    University and the Full Circle Arts Co-op, a 501(c)(3) not-for-profit organization.
</p>
<p>
    This service is guided by the following principles:
</p>
<ul>
    <li>
        <strong>Free:</strong>
        A community calendar should be free for everyone to view and contribute to.
    </li>
    <li>
        <strong>Democratic:</strong>
        The contents and function of a community calendar should be determined by the community.
    </li>
    <li>
        <strong>Egalitarian:</strong>
        All demographics, event types, and genres of expression should be promoted
        equally in order to break down social barriers and unite disparate components of the community.
    </li>
    <li>
        <strong>Decentralized:</strong>
        A decentralized, multifaceted approach to distributing a database of event
        information allows it to reach a wider audience by a variety of methods and with the
        participation of community partners.
    </li>
    <li>
        <strong>Evolving:</strong>
        The development of an online community calendar should be an ongoing process,
        guided by the community that it serves and the advancement of web technology.
    </li>
</ul>

<h2>
    Where Credit's Due
</h2>

<?php
    $credits = [
        'People' => [
            '<a href="mailto:graham@phantomwatson.com">Graham Watson</a>' => 'Web Developer, Administrator',
            'Erica Dee Fox' => 'Web Developer',
            'Benjamin Easley' => 'Graphic Designer',
            'Nicholas Boyum' => 'Artist (map of Muncie background image)'
        ],
        'Organizations' => [
            '<a href="http://fullcircleartscoop.org">Full Circle Arts Co-op</a>' => 'Sponsor',
            '<a href="http://bsu.edu/cber">Center for Business and Economic Research</a>' => 'Sponsor'
        ],
        'Software' => [
            '<a href="http://cakephp.org">CakePHP</a>' => 'Site framework',
            '<a href="http://jquery.com/">jQuery</a>' => 'Javascript framework',
            '<a href="http://jqueryui.com/">jQuery UI</a>' => 'User interface magic',
            '<a href="http://dimsemenov.com/plugins/magnific-popup/">Magnific Popup</a>' => 'Elegant media popups',
            '<a href="http://www.digitalmagicpro.com/jPicker/">jPicker</a>' => 'Color picker',
            '<a href="http://recaptcha.net/">reCAPTCHA</a>' => 'Spam defense',
            '<a href="http://google.com/analytics">Google Analytics</a>' => 'Traffic analysis',
            '<a href="http://www.jetbrains.com/phpstorm/">PhpStorm</a>' => 'IDE of choice',
            '<a href="http://www.atom.io">Atom</a>' => 'Other IDE of choice',
            '<a href="http://travis-ci.org">Travis CI</a>' => 'Testing'
        ]
    ];
?>

<ul id="credits">
    <?php foreach ($credits as $category => $members): ?>
        <li class="category"><?php echo $category ?><br class="break" /></li>
        <?php foreach ($members as $name => $position): ?>
            <li class="row"><div class="name"><?php echo $name ?></div><div class="position"><?php echo $position ?></div><br class="break" /></li>
        <?php endforeach; ?>
        <li class="row" style="border: none;">&nbsp;</li>
    <?php endforeach; ?>
</ul>
