<?php if (isset($prevStartDate)): ?>
    <?php if ($prevStartDate == date('Ymd', strtotime('Today'))): ?>
        <h1 class="page_title">
            <?php echo $category->name; ?>
            <?php echo $this->Icon->category($category->name); ?>
        </h1>
    <?php endif ?>
<?php endif ?>
<?php
    $this->Js->buffer("
        muncieEvents.requestEventFilters.category = '$category->slug';
    ");
    echo $this->element('events/accordion_wrapper');
?>
