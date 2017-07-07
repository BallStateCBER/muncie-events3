<div class="dropdown">
    <?php
        $formTemplate = [
            'inputContainer' => '{{content}}',
            'submitContainer' => '{{content}}'
        ];
        $this->Form->setTemplates($formTemplate);
    ?>
    <?= $this->Form->create('Event', [
            'id' => 'EventSearchForm',
            'url' => array_merge(['action' => 'search'], $this->request->params['pass'])
        ]);
    ?>
    <img src="/img/loading_small_dark.gif" id="search_autocomplete_loading" />
    <?= $this->Form->input('filter', [
        'label' => false,
        'class' => 'form-control'
    ]) ?>
    <div class="input-group-btn">
        <div class="btn-group">
            <?= $this->Form->submit('Search', [
                'class' => 'btn btn-default btn-sm'
            ]) ?>
            <button id="search_options_toggler" class="dropdown-toggle btn btn-secondary btn-sm" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="caret"></span>
                <span class="sr-only">Search options</span>
            </button>
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
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <?= $this->Form->end() ?>
    <?php $this->Js->buffer("setupSearch();"); ?>
</div>
