<h1 class="page_title">Monthly Calendar</h1>

<?php echo $this->Html->link(
    '&larr; Back to Widgets Overview',
    ['action' => 'index'],
    ['escape' => false, 'class' => 'under_header_back']
); ?>

<div class="widget_controls_wrapper">
    <div class="widget_controls">
        <h2>Customize Your Widget</h2>
        <form>
            <h3>
                <a href="#">Events</a>
            </h3>
            <div id="WidgetFilterOptions">
                <div>
                <?php echo $this->element('widgets/customize/events'); ?>

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
                        <option value="<?php echo $n; ?>" <?php if ($n == $defaults['event_options']['events_displayed_per_day']): ?>selected="selected"<?php endif; ?>>
                            <?php echo $n; ?>
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
            </div>

            <h3>
                <a href="#">Text</a>
            </h3>
            <div class="text">
                <div>
                <?php echo $this->element('widgets/customize/text'); ?>
                <label for="WidgetFontSize">
                    Font size:
                </label>
                <input id="WidgetFontSize" value="<?php echo $defaults['styles']['fontSize']; ?>" name="fontSize" type="text" class="style" />
                <p class="text-muted">
                    Size of event titles. Can be in pixels, ems, percentages, or points (e.g. 10px, 0.9em, 90%, 8pt)
                </p>
                </div>
            </div>

            <h3>
                <a href="#">Borders</a>
            </h3>
            <div class="borders">
                <div>
                <?php echo $this->element('widgets/customize/borders'); ?>
                <div class="checkbox">
                    <input type="hidden" name="outerBorder" value="0" />
                    <input type="checkbox" name="outerBorder" checked="checked" value="1" class="option" id="WidgetIframeBorder" />
                    <label for="WidgetIframeBorder">
                        Border around widget
                    </label>
                </div>
                </div>
            </div>

            <h3>
                <a href="#">Backgrounds</a>
            </h3>
            <div class="backgrounds">
                <div>
                <?php echo $this->element('widgets/customize/backgrounds'); ?>
                </div>
            </div>

            <h3>
                <a href="#">Size</a>
            </h3>
            <div>
                <div>
                <label for="WidgetHeight">
                    Height:
                </label>
                <input id="WidgetHeight" value="<?php echo $defaults['iframe_options']['height']; ?>px" name="height" type="text" class="style" />

                <br />

                <label for="WidgetWidth">
                    Width:
                </label>
                <input id="WidgetWidth" value="<?php echo $defaults['iframe_options']['width']; ?>px" name="width" type="text" class="style" />
                <p class="text-muted">
                    Sizes can be in pixels (e.g. 300px) or percentages (e.g. 100%).
                    A <strong>minimum width</strong> of 600px is recommended.
                </p>
                </div>
            </div>

            <br />
            <input type="submit" value="Apply changes" />
        </form>
    </div>
    <div class="widget_demo" id="widget_demo"></div>
    <br class="clear" />
</div>

<?php
    $this->Html->script('/jPicker/jpicker-1.1.6.js', ['inline' => false]);
    $this->Html->css('/jPicker/css/jPicker-1.1.6.min.css', null, ['inline' => false]);
    $this->Html->css('/jPicker/jPicker.css', null, ['inline' => false]);
    $this->Html->script('widgets/customize.js', ['inline' => false]);
    $this->Js->buffer("widgetCustomizer.setupWidgetDemo('month');");
?>
