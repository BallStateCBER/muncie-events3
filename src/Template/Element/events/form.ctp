<?php
    $multipleDatesAllowed = ($this->request->action == 'add' || $this->request->action == 'editSeries');
    echo $this->Html->script('event_form.js', ['inline' => false]);
    $this->Form->setTemplates([
        'inputContainer' => '{{content}}'
    ]);
?>

<h1 class="page_title">
    <?= $titleForLayout; ?>
</h1>

<a href="#posting_rules" id="posting_rules_toggler" data-toggle="collapse">
    Rules for Posting Events
</a>

<div id="posting_rules" class="alert alert-info collapse">
    <?= $this->element('rules'); ?>
</div>

<?php if (!$this->request->session()->read('Auth.User.id')): ?>
    <div class="alert alert-info">
        <p>
            <strong>You're not currently logged in</strong>. You can still submit this event, but...
        </p>
        <ul>
            <li>you will not be able to edit it,</li>
            <li>you will not be able to add custom tags,</li>
            <li>you will not be able to include images,</li>
            <li>you'll have to fill out one of those annoying CAPTCHA challenges, and</li>
            <li>it won't be published until an administrator reviews it.</li>
        </ul>
        <p>
            You can
            <strong>
                <?= $this->Html->link('register an account', ['controller' => 'users', 'action' => 'register']) ?>
            </strong>
            and
            <strong>
                <?= $this->Html->link('log in', ['controller' => 'users', 'action' => 'login']) ?>
            </strong>
            to skip the hassle.
        </p>
    </div>
<?php elseif (isset($autoPublish) && $this->request->getParam('action') == 'add'): ?>
    <div class="alert alert-info">
        <p>
            <strong>Thanks for registering an account!</strong> Unfortunately, to combat spam, your first event will need to be
            approved by an administrator before it gets published. This typically happens in less than 24 hours. But after that,
            all of your events will go directly to the calendar network.
        </p>
    </div>
<?php endif; ?>

<?= $this->Form->create($event, [
    'type' => 'file'
    ]) ?>
    <table class="event_form">
        <tbody>
            <tr>
                <th>
                    Event
                </th>
                <td>
                    <div class='form-group col-lg-8 col-md-10 col-xs-12'>
                        <label class="sr-only" for="title">
                            Title
                        </label>
                        <?= $this->Form->control('title', [
                            'class' => 'form-control',
                            'label' => false
                        ]); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                    Category
                </th>
                <td>
                    <div class='form-group col-lg-8 col-md-10 col-xs-12'>
                        <label class="sr-only" for="category_id">
                            Category
                        </label>
                        <?= $this->Form->control('category_id', [
                            'class' => 'form-control',
                            'label' => false,
                            'options' => $categories
                        ]); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                    Date(s)
                </th>
                <td>
                    <div class="col-xs-12 col-lg-8 col-md-10">
                        <div id="datepicker" class='<?= $multipleDatesAllowed ? 'multi' : 'single'; ?>'></div>
                        <?php
                        if ($multipleDatesAllowed) {
                            echo $this->Html->script('jquery-ui.multidatespicker.js', ['inline' => false]);
                            $this->Js->buffer("
                                var default_date = '".$defaultDate."';
                                var preselected_dates = $preselectedDates;
                                setupDatepickerMultiple(default_date, preselected_dates);
                            ");
                        } else {
                            $this->Js->buffer("
                                var default_date = '".$event->date."';
                                setupDatepickerSingle(default_date);
                            ");
                        }
                        echo $this->Form->input('date', [
                            'id' => 'datepicker_hidden',
                            'type' => 'hidden'
                        ]);
                        ?>
                        <?php if ($multipleDatesAllowed): ?>
                            <div class="text-muted" id="datepicker_text">
                                Select more than one date to create multiple events connected by a series.
                            </div>
                            <?= $this->Form->input('series_id', [
                                'type' => 'hidden'
                            ]); ?>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php if ($has['series']): ?>
                <tr id="series_row">
                    <th>Series Name</th>
                    <label class="sr-only" for="EventSeriesTitle">
                        Series Name
                    </label>
                    <td>
                        <div class='form-group col-lg-8 col-md-10 col-xs-12'>
                            <?= $this->Form->input('series_title', [
                                'label' => false,
                                'class' => 'form-control',
                                'id' => 'EventSeriesTitle'
                            ]); ?>
                            <div class="text-muted">
                                By default, the series and its events have the same title.
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            <tr>
                <th>
                    Time
                </th>
                <td>
                    <label class="sr-only" for="time_start.hour">
                        Hour
                    </label>
                    <label class="sr-only" for="time_start.minute">
                        Minute
                    </label>
                    <label class="sr-only" for="time_start.meridian">
                        AM or PM
                    </label>
                    <div id="eventform_timestart_div" class="form-group col-md-10 col-xs-12">
                        <?= $this->Form->time('time_start', [
                            'label' => false,
                            'interval' => 5,
                            'timeFormat' => '12',
                            'hour' => [
                                'class' => 'form-control event_time_form'
                            ],
                            'minute' => [
                                'class' => 'form-control event_time_form'
                            ],
                            'meridian' => [
                                'class' => 'form-control event_time_form'
                            ]
                        ],
                        'form-control event_time_form'); ?>
                        <span id="eventform_noendtime"<?=$has['end_time'] ? ' style="display: none;"' : ''?>>
                            <a id="add_end_time" href="#">Add end time</a>
                        </span>
                    </div>
                    <div id="eventform_hasendtime" class="form-group col-md-10 col-xs-12" <?php if (!$has['end_time']): ?>style="display: none;"<?php endif; ?>>
                        <label class="sr-only" for="time_end[hour]">
                            Hour
                        </label>
                        <label class="sr-only" for="time_end.minute">
                            Minute
                        </label>
                        <label class="sr-only" for="time_end.meridian">
                            AM or PM
                        </label>
                        <?php
                            if (isset($event['time_end'])) {
                                $selected_end_time = ($event['time_end'] == '00:00:00')
                                    ? '24:00:00'    // Fixes bug where midnight is represented as noon
                                    : $event['time_end'];
                            }
                            echo $this->Form->time('time_end', [
                                #'label' => false,
                                'interval' => 5,
                                'timeFormat' => '12',
                                'hour' => [
                                    'class' => 'form-control event_time_form',
                                    'label' => true
                                ],
                                'minute' => [
                                    'class' => 'form-control event_time_form'
                                ],
                                'meridian' => [
                                    'class' => 'form-control event_time_form'
                                ]
                            ]);
                        ?>
                        <?= $this->Form->hidden('has_end_time', [
                            'id' => 'eventform_hasendtime_boolinput',
                            'value' => $has['end_time'] ? 1 : 0
                        ]); ?>
                        <a href="#" id="remove_end_time">Remove end time</a>
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                    Location
                </th>
                <td>
                    <label class="sr-only" for="location">
                        Location
                    </label>
                    <div class='form-group col-lg-8 col-md-10 col-xs-12'>
                        <?= $this->Form->control('location', [
                            'class' => 'form-control',
                            'label' => false
                        ]); ?>
                        <label class="sr-only" for="location-details">
                            Location details
                        </label>
                        <?= $this->Form->control('location_details', [
                            'class' => 'form-control',
                            'label' => false,
                            'placeholder' => 'Location details (e.g. upstairs, room 149, etc.)'
                        ]); ?>
                        <a href="#" id="eventform_noaddress" <?= $has["address"] ? "style=\'display: none;\'" : ""?>>Add address</a>
                        <a id="location_tips" data-toggle="popover" title="List of Ball State locations" data-content="
                            Put room numbers in 'location details', and format location names as such:
                            <br />
                            <i>[building name], Ball State University</i>.
                            <br />
                            <i>e.g. Art and Journalism Building, Ball State University, as opposed to 'AJ 175' or 'BSU AJ'.</i>
                            <br />
                            <a href='https://cms.bsu.edu/map/building-list' target='blank'>Click here<span class='sr-only'>for a list of Ball State location names.</span></a>
                            for a list of Ball State location names.
                            <a data-toggle='collapse' href='#collapseExample' role='button' aria-expanded='false' aria-controls='collapseExample'>
                                Why so many rules?
                            </a>
                            <div class='collapse' id='collapseExample'>
                                <div class='card card-body'>
                                    Ball State has numerous locations that can be described many different ways. However, in order to keep
                                    our location list neat & uniform, we ask that location names, room numbers, and details be looked up and formatted
                                    accordingly. By keeping our list tidy, it makes promoting new events,
                                    and archiving old events, much simpler.
                                </div>
                            </div>
                        ">Ball State location?</a>
                        <?php
                        $this->Js->buffer("
                            $(function () {
                            $('[data-toggle=\"popover\"]').popover({html:true})
                            })
                        ");
                        ?>
                    </div>
                </td>
            </tr>
            <tr id="eventform_address" <?php if (!$has['address']): ?>style="display: none;"<?php endif; ?>>
                <th>
                    Address
                </th>
                <td>
                    <label class="sr-only" for="EventAddress">
                        Address
                    </label>
                    <div class='form-group col-lg-8 col-md-10 col-xs-12'>
                        <?= $this->Form->control('address', [
                            'class' => 'form-control',
                            'label' => false,
                            'id' => 'EventAddress'
                        ]); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                    Description
                </th>
                <td>
                    <label class="sr-only" for="EventDescription">
                        Description
                    </label>
                    <script src="/emojione/lib/js/emojione.min.js"></script>
                    <div class='form-group col-lg-8 col-md-10 col-xs-12'>
                        <?= $this->CKEditor->loadJs(); ?>
                        <?= $this->Form->control('description', [
                            'label' => false,
                            'id' => 'EventDescription'
                        ]); ?>
                        <script>
                            CKEDITOR.plugins.addExternal('emojione', '/ckeditor-emojione/', 'plugin.js');
                            CKEDITOR.config.extraPlugins = 'emojione';
                        </script>
                        <?= $this->CKEditor->replace('description'); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                    Tags
                </th>
                <td id="eventform_tags">
                    <div class='form-group col-lg-8 col-md-10 col-xs-12'>
                        <?= $this->element('tags/tag_editing', [
                            'availableTags' => $availableTags,
                            'event' => $event,
                            'hide_label' => true
                        ]); ?>
                    </div>
                </td>
            </tr>
            <?php if ($this->request->session()->read('Auth.User.id')): ?>
                <tr>
                    <th>
                        Images
                    </th>
                    <td>
                        <div class="form-group col-xs-12">
                            <?= $this->element('images/form'); ?>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            <tr id="eventform_nocost"<?= ($has['cost']) ? ' style="display: none;"' : ''; ?>>
                <td>
                    <a href="#" id="event_add_cost">
                        Add cost
                    </a>
                </td>
            </tr>
            <tr id="eventform_hascost"<?= (!$has['cost']) ? ' style="display: none;"' : ''; ?>>
                <th>Cost</th>
                <td>
                    <label class="sr-only" for="EventCost">
                        Cost
                    </label>
                    <div class='form-group col-lg-8 col-md-10 col-xs-12'>
                        <?= $this->Form->input('cost', [
                            'maxLength' => 200,
                            'label' => false,
                            'class' => 'form-control',
                            'id' => 'EventCost'
                        ]); ?>
                        <a href="#" id="event_remove_cost">Remove</a>
                        <div class="text-muted">Just leave this blank if the event is free.</div>
                    </div>
                </td>
            </tr>
            <tr id="eventform_noages"<?= ($has['ages']) ? ' style="display: none;"' : ''; ?>>
                <td>
                    <a href="#" id="event_add_age_restriction">
                        Add age restriction
                    </a>
                </td>
            </tr>
            <tr id="eventform_hasages"<?= (!$has['ages']) ? ' style="display: none;"' : ''; ?>>
                <th>Age Restriction</th>
                <td>
                    <label class="sr-only" for="EventAgeRestriction">
                        Age Restriction
                    </label>
                    <div class='form-group col-lg-8 col-md-10 col-xs-12'>
                        <?= $this->Form->input('age_restriction', [
                            'label' => false,
                            'class' => 'form-control',
                            'maxLength' => 30,
                            'id' => 'EventAgeRestriction'
                        ]); ?>
                        <a href="#" id="event_remove_age_restriction">Remove</a>
                        <div class="text-muted">Leave this blank if this event has no age restrictions.</div>
                    </div>
                </td>
            </tr>
            <tr id="eventform_nosource"<?= ($has['source']) ? ' style="display: none;"' : ''; ?>>
                <td>
                    <a href="#" id="event_add_source">
                        Add info source
                    </a>
                </td>
            </tr>
            <tr id="eventform_hassource"<?= (!$has['source']) ? ' style="display: none;"' : ''; ?>>
                <th>Source</th>
                <td>
                    <label class="sr-only" for="EventSource">
                        Source
                    </label>
                    <div class='form-group col-lg-8 col-md-10 col-xs-12'>
                        <?= $this->Form->input('source', [
                            'label' => false,
                            'class' => 'form-control',
                            'id' => 'EventSource'
                        ]); ?>
                        <a href="#" id="event_remove_source">Remove</a>
                        <div class="text-muted">Did you get this information from a website, newspaper, flyer, etc?</div>
                    </div>
                </td>
            </tr>
            <?php if ($this->request->params['action'] == 'add' && !$this->request->session()->read('Auth.User.id')): ?>
                <tr>
                    <th>Spam Protection</th>
                    <td>
                        <?= $this->Recaptcha->display() ?>
                    </td>
                </tr>
            <?php endif; ?>
            <tr>
                <th>
                    <label class="sr-only" for="submit">
                        Ready to Submit?
                    </label>
                </th>
                <td>
                    <?php if ($this->request->params['action'] == 'add' && !$this->request->session()->read('Auth.User.id')): ?>
                        <?= $this->Form->submit('Submit') ?>
                    <?php else: ?>
                        <?= $this->Form->submit('Submit', ['class'=>'btn btn-secondary']) ?>
                    <?php endif ?>
                </td>
            </tr>
        </tbody>
    </table>
<?= $this->Form->end() ?>
<?php
    $previous_locations_for_autocomplete = [];
    foreach ($previousLocations as $location => $address) {
        $previous_locations_for_autocomplete[] = [
            'label' => $location,
            'value' => $address
        ];
    }
    $this->Js->buffer('
        eventForm.previousLocations = '.$this->Js->object($previous_locations_for_autocomplete).';
        setupEventForm();
    ');
?>
