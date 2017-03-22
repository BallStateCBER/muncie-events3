<div class="events form large-9 medium-8 columns content">
    <?= $this->Form->create($event) ?>
    <fieldset>
        <legend><?= __('Add Event') ?></legend>
        <?php
            echo $this->Form->control('title');
            echo $this->Form->control('description');
            echo $this->Form->control('location');
            echo $this->Form->control('location_details');
            echo $this->Form->control('address');
            echo $this->Form->control('user_id', ['options' => $users, 'empty' => true]);
            echo $this->Form->control('category_id', ['options' => $categories]);
            echo $this->Form->control('series_id');
            echo $this->Form->control('date');
            echo $this->Form->control('time_start');
            echo $this->Form->control('time_end', ['empty' => true]);
            echo $this->Form->control('age_restriction');
            echo $this->Form->control('cost');
            echo $this->Form->control('source');
            echo $this->Form->control('published');
            echo $this->Form->control('approved_by');
            echo $this->Form->control('images._ids', ['options' => $images]);
            echo $this->Form->control('tags._ids', ['options' => $tags]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
