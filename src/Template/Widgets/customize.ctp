<h1 class="page_title"><?= $titleForLayout ?></h1>

<?= $this->Html->link(
    '&larr; Back to Widgets Overview',
    array('action' => 'index'),
    array('escape' => false, 'class' => 'under_header_back')
); ?>

<div class="widget_controls_wrapper">
    <div class="widget_controls form-group col-lg-4">
        <h2>Customize Your Widget</h2>
        <form>
            <h3>
                <a href="#">Events</a>
            </h3>
            <div id="WidgetFilterOptions">
                <?= $this->element('widgets/customize/events'); ?>

                <?php if ($this->request->action == 'customizeMonth'): ?>
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
                    <p class="text-muted">
                        Additional events will be hidden under a "X more events" link.
                    </p>
                <?php endif; ?>

            </div>

            <h3>
                <a href="#">Text</a>
            </h3>
            <div class="text">
                <?= $this->element('widgets/customize/text'); ?>
            </div>

            <h3>
                <a href="#">Borders</a>
            </h3>
            <div class="borders">
                <?= $this->element('widgets/customize/borders'); ?>
                <div class="form-control">
                    <input type="checkbox" name="outerBorder" checked="checked" value="1" class="option" /> Border around widget
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
<<<<<<< HEAD
            <input class="btn" type="submit" value="Apply changes" />
=======
            <input class="btn btn-small" type="submit" value="Apply changes" />
>>>>>>> 9c724c5a96b2752367e91b3c7e0ed7aa4a47a62e
        </form>
    </div>
    <div class="widget_demo col-lg-7" id="widget_demo"></div>
</div>

<?= $this->Html->script('/jPicker/jpicker-1.1.6.js'); ?>
<?= $this->Html->css('/jPicker/css/jPicker-1.1.6.min.css'); ?>
<?= $this->Html->css('/jPicker/jPicker.css'); ?>
<?= $this->Html->script('widgets/customize.js'); ?>
<?= $this->Html->scriptBlock("widgetCustomizer.setupWidgetDemo('$type');", ['defer' => true]); ?>
