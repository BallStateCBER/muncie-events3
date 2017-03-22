<?php
	$total_pages = $this->Paginator->counter(array('format' => '{:pages}'));
	$current_page = $this->Paginator->counter(array('format' => '{:page}'));
	$paginator_url = urldecode(Router::url(array_merge(
		$this->request->params['named'],
		$this->request->params['pass'],
		array('page' => '{page}')	// Replaced by JS with the appropriate page number
	), true));
?>
<?php if ($total_pages > 1): ?>

	<ul class="pagination">

		<li class="page-item">
			<?php
				$first = $this->Paginator->first("&laquo; First", array('escape' => false, 'class' => 'page-link'));
				echo $first ? $first : "<a class='page-link'>&laquo; First</a>";
			?>
		</li>

		<li class="page-item">
			<?php
				$prev = $this->Paginator->prev("&lsaquo; Prev", array('escape' => false, 'class' => 'page-link'));
				echo $prev ? $prev : "<a class='page-link'>&lsaquo; Prev</a>";
			?>
		</li>

		<select class="paginator_select custom-select" data-url="<?php echo $paginator_url; ?>">
			<?php for ($p = 1; $p <= $total_pages; $p++): ?>
				<option <?php if ($p == $current_page): ?>selected="selected"<?php endif; ?>>
					<?php echo $p; ?>
				</option>
			<?php endfor; ?>
		</select>

		<?php $this->Js->buffer("setupPagination();"); ?>

		<li class="page-item">
			<?php
				$next = $this->Paginator->next("Next &rsaquo;", array('escape' => false, 'class' => 'page-link'));
				echo $next ? $next : "<a class='page-link'>Next &rsaquo;</a>";
			?>
		</li>

		<li class="page-item">
			<?php
				$last = $this->Paginator->last("Last &raquo;", array('escape' => false, 'class' => 'page-link'));
				echo $last ? $last : "<a class='page-link'>Last &raquo;</a>";
			?>
		</li>

	</ul>
	
<?php endif; ?>
