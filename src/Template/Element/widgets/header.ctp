<ul class="header">
    <li>
        <a href="http://muncieevents.com"><i class="icon icon-me-logo"></i>MuncieEvents.com</a>
    </li>
    <?php if (!empty($filters)): ?>
        <li>
            <a href="#" id="filter_info_toggler">Filters</a>
            <?php $this->Js->buffer("
                $('#filter_info_toggler').click(function (event) {
                    event.preventDefault();
                    $('#widget_filters').slideToggle('fast');
                });
            "); ?>
        </li>
    <?php endif; ?>
    <li>
        <?php echo $this->Html->link('Add Event', array('controller' => 'events', 'action' => 'add')); ?>
    </li>
</ul>
<?php if (!empty($filters)): ?>
    <div id="widget_filters" style="display: none;">
        <div>
            Currently showing only the following kinds of events:
            <ul>
                <?php if (isset($filters['category'])): ?>
                    <li>
                        <strong>
                            <?php echo count($filters['category']) == 1 ? 'Category' : 'Categories'; ?>:
                        </strong>
                        <?php
                            $category_names = array();
                            foreach ($filters['category'] as $cat_id) {
                                $category_names[] = $categories[$cat_id];
                            }
                            echo $this->Text->toList($category_names);
                        ?>
                    </li>
                <?php endif; ?>
                <?php if (isset($filters['location'])): ?>
                    <li>
                        <strong>
                            Location:
                        </strong>
                        <?php echo $filters['location']; ?>
                    </li>
                <?php endif; ?>
                <?php if (isset($filters['tags_included'])): ?>
                    <li>
                        <strong>
                            With <?php echo count($filters['tags_included']) == 1 ? 'tag' : 'tags'; ?>:
                        </strong>
                        <?php echo $this->Text->toList($filters['tags_included']); ?>
                    </li>
                <?php endif; ?>
                <?php if (isset($filters['tags_excluded'])): ?>
                    <li>
                        <strong>
                            Without <?php echo count($filters['tags_excluded']) == 1 ? 'tag' : 'tags'; ?>:
                        </strong>
                        <?php echo $this->Text->toList($filters['tags_excluded']); ?>
                    </li>
                <?php endif; ?>
            </ul>
            <?php
                echo $this->Html->link(
                    '[View all events]',
                    $all_events_url,
                    array('target' => '_self')
                );
            ?>
        </div>
    </div>
<?php endif; ?>
