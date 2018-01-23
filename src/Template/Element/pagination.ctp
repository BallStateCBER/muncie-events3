<?php
if ($this->request->action) {
    $slug = '';
}

$detail = '';
$direction = $this->request->getParam('action') != 'view' ? strtolower($direction) : '';
$trait = '';
if ($this->request->getParam('action') == 'location') {
    $detail = $location;
    $trait = 'at';
}
if ($this->request->getParam('action') == 'tag') {
    $detail = '"' . $tag['name'] . '"';
    $trait = 'tagged as';
}
if ($this->request->getParam('controller') == 'Users' && $this->request->getParam('action') == 'view') {
    $detail = $user['name'];
    $trait = 'posted by';
}

$count = $this->Paginator->counter(['format' => __('{{current}}')]);
$count = intval($count);
$s = $count == 1 ? '' : 's';

$totalPages = $this->Paginator->counter(['format' => '{{pages}}']);
    $currentPage = $this->Paginator->counter(['format' => '{{page}}']);
    $paginatorUrl = urldecode($this->Url->build([
        'controller' => "events",
        'action' => $this->request->action, $slug, $direction,
        'page' => '{page}'
    ]))
?>
<div class="paginator">
    <ul class="pagination">
        <?php
            $first = $this->Paginator->first("&laquo; First", ["escape" => false, "class" => "page-link"]);
            echo $first ? $first : '<span class="page-link">&laquo; First</span>';
        ?>
        <?php
            $prev = $this->Paginator->prev("&lsaquo; Prev", ["escape" => false, "class" => "page-link"]);
            echo $this->Paginator->hasPrev() ? $prev : '<span class="page-link">&lsaquo; Prev</span>';
        ?>
        <select class="paginator_select custom-select" data-url="<?= $paginatorUrl; ?>">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <option <?php if ($p == $currentPage): ?>selected="selected"<?php endif; ?>>
                    <?= $p; ?>
                </option>
            <?php endfor; ?>
        </select>
        <?php $this->Js->buffer("setupPagination();"); ?>
        <?php
            $next = $this->Paginator->next("Next &rsaquo;", ["escape" => false, "class" => "page-link"]);
            echo $this->Paginator->hasNext() ? $next : '<span class="page-link">Next &rsaquo;</span>';
        ?>
        <?php
            $last = $this->Paginator->last("Last &raquo;", ["escape" => false, "class" => "page-link"]);
            echo $last ? $last : '<span class="page-link">Last &raquo;</span>';
        ?>
    </ul>
    <p><?= $this->Paginator->counter(['format' => __("Showing $count $direction event$s $trait $detail")]) ?></p>
</div>
