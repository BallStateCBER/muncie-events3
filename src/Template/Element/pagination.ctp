<?php
if ($this->request->action) {
    $slug = '';
}
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
            echo $first ? $first : "<span class='page-link'>&laquo; First</span>";
        ?>
        <?php
            $prev = $this->Paginator->prev("&lsaquo; Prev", ["escape" => false, "class" => "page-link"]);
            echo $prev ? $prev : "<span class='page-link'>&lsaquo; Prev</span>";
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
            echo $next ? $next : "<span class='page-link'>Next &rsaquo;</span>";
        ?>
        <?php
            $last = $this->Paginator->last("Last &raquo;", ["escape" => false, "class" => "page-link"]);
            echo $last ? $last : "<span class='page-link'>Last &raquo;</span>";
        ?>
    </ul>
    <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
</div>
