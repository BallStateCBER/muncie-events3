<div class="dropdown">
    <?= $this->Form->create('Event', [
            'id' => 'EventSearchForm',
            # 'url' => array_merge(['action' => 'search'], $this->request->params['pass'])
        ]);
    ?>
    <?= $this->Form->input('filter', [
            'div' => false,
            'label' => false,
            'class' => 'form-control',
            'before' => '<img src="/img/loading_small_dark.gif" id="search_autocomplete_loading" />',
            'after' => $this->Form->submit(__('Search'), ['div' => false, 'class' => 'btn btn-secondary btn-sm']).'',
        ]);
    ?>
    <button id="search_options_toggler" class="dropdown-toggle btn btn-secondary btn-sm" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Advanced</button>
    <div id="search_options" class="dropdown-menu" aria-labelledby="search_options_toggler">
        <div>
            <?= $this->Form->input('direction', [
                    'options' => [
                        'future' => 'Upcoming Events',
                        'past' => 'Past Events',
                        'all' => 'All Events'
                    ],
                    'default' => 'future',
                    'type' => 'radio',
                    'legend' => false,
                    'separator' => '<br />'
                ]);
            ?>
        </div>
    </div>
    <?= $this->Form->end(); ?>
    <?php $this->Js->buffer("setupSearch();"); ?>
</div>
