<h1 class="page_title">Monthly Calendar</h1>

<?= $this->Html->link(
    '&larr; Back to Widgets Overview',
    array('action' => 'index'),
    array('escape' => false, 'class' => 'under_header_back')
); ?>

<div class="widget_controls_wrapper">
    <div class="widget_controls col-lg-4">
        <h2>Customize Your Widget</h2>
        <form>
            <h3>
                <a href="#">Events</a>
            </h3>
            <div id="WidgetFilterOptions">
                <?= $this->element('widgets/customize/events'); ?>

                <div class="checkbox">
                    <input type="hidden" name="showIcons" value="0" />
                    <input type="checkbox" name="showIcons" checked="checked" value="1" class="option" id="WidgetShowIcons" />
                    <label for="WidgetShowIcons">
                        Show category icons
                    </label>
                </div>

                <div class="checkbox" id="WidgetHideGEIcon_wrapper">
                    <input type="hidden" name="hideGeneralEventsIcon" value="0" />
                    <input type="checkbox" name="hideGeneralEventsIcon" value="1" class="option" id="WidgetHideGEIcon" />
                    <label for="WidgetHideGEIcon">
                        But not the 'General Events' icon
                    </label>
                </div>

                <label for="WidgetEventsDisplayedPerDay">
                    Events shown per day:
                </label>
                <select id="WidgetEventsDisplayedPerDay" name="events_displayed_per_day">
                    <?php for ($n = 1; $n <= 10; $n++): ?>
                        <option value="<?= $n; ?>" <?php if ($n == $defaults['event_options']['events_displayed_per_day']): ?>selected="selected"<?php endif; ?>>
                            <?= $n; ?>
                        </option>
                    <?php endfor; ?>
                    <option value="0">
                        Unlimited
                    </option>
                </select>
                <p class="text-muted">
                    Additional events will be hidden under a "X more events" link.
                </p>
            </div>

            <h3>
                <a href="#">Text</a>
            </h3>
            <div class="text">
                <?= $this->element('widgets/customize/text'); ?>
                <div class="form-control">
                    <label for="WidgetFontSize">
                        Font size:
                    </label>
                    <input id="WidgetFontSize" value="<?= $defaults['styles']['fontSize']; ?>" name="fontSize" type="text" class="style" />
                    <p class="text-muted">
                        Size of event titles. Can be in pixels, ems, percentages, or points (e.g. 10px, 0.9em, 90%, 8pt)
                    </p>
                </div>
            </div>

            <h3>
                <a href="#">Borders</a>
            </h3>
            <div class="borders">
                <?= $this->element('widgets/customize/borders'); ?>
                <div class="checkbox form-control">
                    <input type="hidden" name="outerBorder" value="0" />
                    <input type="checkbox" name="outerBorder" checked="checked" value="1" class="option" id="WidgetIframeBorder" />
                    <label for="WidgetIframeBorder">
                        Border around widget
                    </label>
                </div>
            </div>

            <h3>
                <a href="#">Backgrounds</a>
            </h3>
            <div class="backgrounds">
                <?= $this->element('widgets/customize/backgrounds'); ?>
            </div>

            <h3>
                <a href="#">Size</a>
            </h3>
            <div>
                <?= $this->element('widgets/customize/size'); ?>
            </div>

            <br />
            <input class="btn btn-small" type="submit" value="Apply changes" />
        </form>
    </div>
    <div class="widget_demo col-lg-7" id="widget_demo"></div>
</div>

<?= $this->Html->script('/jPicker/jpicker-1.1.6.js'); ?>
<?= $this->Html->css('/jPicker/css/jPicker-1.1.6.min.css'); ?>
<?= $this->Html->css('/jPicker/jPicker.css'); ?>
<?= $this->Html->script('widgets/customize.js'); ?>
<?= $this->Html->scriptBlock("widgetCustomizer.setupWidgetDemo('feed');", ['defer' => true]); ?>
