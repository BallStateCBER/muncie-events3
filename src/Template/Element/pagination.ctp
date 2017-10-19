<?php
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
            $first = $this->Paginator->first("&laquo; First", ['escape' => false, 'class' => 'page-link']);
            echo $first ? $first : "<a class='page-link'>&laquo; First</a>";
        ?>
        <?php
            $prev = $this->Paginator->prev("&lsaquo; Prev", ['escape' => false, 'class' => 'page-link']);
            echo $prev ? $prev : "<a class='page-link'>&lsaquo; Prev</a>";
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
            $next = $this->Paginator->next("Next &rsaquo;", ['escape' => false, 'class' => 'page-link']);
            echo $next ? $next : "<a class='page-link'>Next &rsaquo;</a>";
        ?>
        <?php
            $last = $this->Paginator->last("Last &raquo;", ['escape' => false, 'class' => 'page-link']);
            echo $last ? $last : "<a class='page-link'>Last &raquo;</a>";
        ?>
    </ul>
    <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
</div>
