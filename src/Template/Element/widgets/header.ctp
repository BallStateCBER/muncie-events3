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
        <?= $this->Html->link('Add Event', ['controller' => 'events', 'action' => 'add']); ?>
    </li>
</ul>
<?php if (!empty($filters)): ?>
    <div id="widget_filters" style="display: none;">
        <div>
            Currently showing only the following kinds of events:
            <ul>
                <?php if (isset($filters['category_id'])): ?>
                    <li>
                        <strong>
                            <?= count($filters['category_id']) == 1 ? 'Category' : 'Categories'; ?>:
                        </strong>
                        <?php
                            $category_names = [];
                            foreach ($filters['category_id'] as $cat_id) {
                                $category_names[] = $cat_id;
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
                        <?= $filters['location']; ?>
                    </li>
                <?php endif; ?>
                <?php if (isset($filters['tags_included'])): ?>
                    <li>
                        <strong>
                            With <?= count($filters['tags_included']) == 1 ? 'tag' : 'tags'; ?>:
                        </strong>
                        <?= $this->Text->toList($filters['tags_included']); ?>
                    </li>
                <?php endif; ?>
                <?php if (isset($filters['tags_excluded'])): ?>
                    <li>
                        <strong>
                            Without <?= count($filters['tags_excluded']) == 1 ? 'tag' : 'tags'; ?>:
                        </strong>
                        <?= $this->Text->toList($filters['tags_excluded']); ?>
                    </li>
                <?php endif; ?>
            </ul>
            <?php
                echo $this->Html->link(
                    '[View all events]',
                    $all_events_url,
                    ['target' => '_self']
                );
            ?>
        </div>
    </div>
<?php endif; ?>
