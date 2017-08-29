<?php
    $multipleDatesAllowed = ($this->request->params['action'] == 'add' || $this->request->params['action'] == 'editseries');
    echo $this->Html->script('event_form.js', ['inline' => false]);
?>

<h1 class="page_title">
    <?= $titleForLayout; ?>
</h1>

<a href="#posting_rules" id="posting_rules_toggler" data-toggle="collapse">
    Rules for Posting Events
</a>

<div id="posting_rules" class="alert alert-info collapse" aria-expanded="false">
    <?= $this->element('rules'); ?>
</div>

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
                    <div class="form-group col-lg-8 col-xs-12">
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
                    <div class="form-group col-lg-8 col-xs-12">
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
                    <div class="col-xs-12 col-lg-8">
                        <div id="datepicker" class="<?= ($multipleDatesAllowed ? 'multi' : 'single'); ?>"></div>
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
                            <div class="text-muted">
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
                    <td>
                        <div class="form-group col-lg-8 col-xs-12">
                            <?= $this->Form->input('event_series.title', [
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
                        <?php
                            if (isset($event['time_end'])) {
                                $selected_end_time = ($event['time_end'] == '00:00:00')
                                    ? '24:00:00'    // Fixes bug where midnight is represented as noon
                                    : $event['time_end'];
                            }
                            echo $this->Form->input('time_end', [
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
                    <div class="form-group col-lg-8 col-xs-12">
                        <?= $this->Form->control('location', [
                            'class' => 'form-control',
                            'label' => false
                        ]); ?>
                        <?= $this->Form->control('location_details', [
                            'class' => 'form-control',
                            'label' => false,
                            'placeholder' => 'Location details (e.g. upstairs, room 149, etc.)'
                        ]); ?>
                        <a href="#" id="eventform_noaddress" <?= $has["address"] ? "style=\'display: none;\'" : ""?>>Add address</a>
                    </div>
                </td>
            </tr>
            <tr id="eventform_address" <?php if (!$has['address']): ?>style="display: none;"<?php endif; ?>>
                <th>
                    Address
                </th>
                <td>
                    <div class="form-group col-lg-8 col-xs-12">
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
                    <script src="/emojione/lib/js/emojione.min.js"></script>
                    <div class="form-group col-lg-8 col-xs-12">
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
                    <div class="form-group col-lg-8 col-xs-12">
                        <?= $this->element('tags/tag_editing', [
                            'availableTags' => $availableTags,
                            'selectedTags' => isset($event->tags) ? $event->tags : [],
                            'event' => $event,
                            'hide_label' => true
                        ]); ?>
                    </div>
                </td>
            </tr>
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
            <tr id="eventform_nocost"<?= ($has['cost']) ? ' style="display: none;"' : ''; ?>>
                <td></td>
                <td>
                    <a href="#" id="event_add_cost">
                        Add cost
                    </a>
                </td>
            </tr>
            <tr id="eventform_hascost"<?= (!$has['cost']) ? ' style="display: none;"' : ''; ?>>
                <th>Cost</th>
                <td>
                    <div class="form-group col-lg-8 col-xs-12">
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
                <td></td>
                <td>
                    <a href="#" id="event_add_age_restriction">
                        Add age restriction
                    </a>
                </td>
            </tr>
            <tr id="eventform_hasages"<?= (!$has['ages']) ? ' style="display: none;"' : ''; ?>>
                <th>Age Restriction</th>
                <td>
                    <div class="form-group col-lg-8 col-xs-12">
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
                <td></td>
                <td>
                    <a href="#" id="event_add_source">
                        Add info source
                    </a>
                </td>
            </tr>
            <tr id="eventform_hassource"<?= (!$has['source']) ? ' style="display: none;"' : ''; ?>>
                <th>Source</th>
                <td>
                    <div class="form-group col-lg-8 col-xs-12">
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
                        <div class="g-recaptcha" data-sitekey="6Lcg6tkSAAAAALkenFi1dIQ5B-4BVLJur5hYl-2J"></div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
<<<<<<< HEAD
<?= $this->Form->button(__('Submit', ['class'=>'btn btn-secondary'])) ?>
=======
<?= $this->Form->button(__('Submit')) ?>
>>>>>>> 9c724c5a96b2752367e91b3c7e0ed7aa4a47a62e
<?= $this->Form->end() ?>
<?php
    $previous_locations_for_autocomplete = [];
    foreach ($previous_locations as $location => $address) {
        $previous_locations_for_autocomplete[] = [
            'label' => $location,
            'value' => $address
        ];
    }
    $this->Js->buffer('
        eventForm.previousLocations = '.$this->Js->object($previous_locations_for_autocomplete).';
        setupEventForm();
    ');
    $this->Js->buffer('
        setupEventForm();
    ');
?>
