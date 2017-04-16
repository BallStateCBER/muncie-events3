<h1 class="page_title">
	<?php echo $title_for_layout; ?>
</h1>

<div id="tag_view_options">
	<table>
		<tr>
			<th>Time</th>
			<td class="direction">
				<?php foreach (array('upcoming', 'past') as $dir): ?>
					<?php echo $this->Html->link(
						ucfirst($dir).' Events',
						array(
							'controller' => 'tags',
							'action' => 'index',
							($dir == 'upcoming' ? 'future' : 'past')
						),
						array(
							'class' => ($direction_adjective == $dir ? 'selected' : ''),
						)
					); ?>
				<?php endforeach; ?>
			</td>
		</tr>
		<tr>
			<th>Categories</th>
			<td class="categories">
				<ul>
					<li>
						<?php echo $this->Html->link(
							'All Categories',
							array(
								'controller' => 'tags',
								'action' => 'index',
								$direction
							),
							array(
								'data-category' => 'all',
								'class' => ($category == 'all' ? 'selected' : '')
							)
						); ?>
					</li>
					<?php foreach ($categories as $cat): ?>
						<?php if (in_array($cat['Category']['id'], $categories_with_tags)): ?>
							<li>
								<?php echo $this->Html->link(
									$this->Icon->category($cat['Category']['name']),
									array(
										'controller' => 'tags',
										'action' => 'index',
										$direction,
										$cat['Category']['id']
									),
									array(
										'title' => $cat['Category']['name'],
										'class' => ($category == $cat['Category']['id'] ? 'selected' : ''),
										'escape' => false
									)
								); ?>
							</li>
						<?php else: ?>
							<li class="no_tags">
								<?php echo $this->Icon->category($cat['Category']['name']); ?>
							</li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
				<?php $this->Js->buffer("
					$('#tag_view_options .categories a').tooltip({
						show: 100,
						hide: 200
					});
				"); ?>
			</td>
		</tr>
		<tr>
			<th>Breakdown</th>
			<td class="breakdown">
				<ul>
					<li>
						<?php echo $this->Html->link(
							'All Tags',
							'#',
							array(
								'title' => 'View tag cloud',
								'data-tag-list' => 'cloud',
								'class' => 'selected'
							)
						); ?>
					</li>
					<?php $letters = array_merge(range('a', 'z'), array('#')); ?>
					<?php foreach ($letters as $letter): ?>
						<li>
							<?php if (isset($tags_by_first_letter[$letter])): ?>
								<?php echo $this->Html->link(
									strtoupper($letter),
									'#',
									array(
										'title' => 'View only tags for '.$direction_adjective.' events beginning with '.strtoupper($letter),
										'data-tag-list' => $letter
									)
								); ?>
							<?php else: ?>
								<span title="No tags for <?php echo $direction_adjective; ?> events beginning with <?php echo strtoupper($letter); ?>">
									<?php echo strtoupper($letter); ?>
								</span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</td>
		</tr>
	</table>
</div>

<div id="tag_index_cloud">
	<?php if (empty($tags)): ?>
		<p class="alert alert-info">
			No tags found for any <?php echo $direction_adjective; ?> events.
		</p>
	<?php else: ?>
	    <?php
            $min_count = $max_count = null;
            foreach ($tags as $tag_name => $tag) {
                if ($min_count == null) {
                    $min_count = $max_count = $tag['count'];
                }
                if ($tag['count'] < $min_count) {
                    $min_count = $tag['count'];
                }
                if ($tag['count'] > $max_count) {
                    $max_count = $tag['count'];
                }
            }
            $count_range = max($max_count - $min_count, 1);
            $min_font_size = 75;
            $max_font_size = 150;
            $font_size_range = $max_font_size - $min_font_size;
        ?>
		<?php foreach ($tags as $tag_name => $tag): ?>
			<?php
                $font_size = log($tag['count']) / log($max_count) * $font_size_range + $min_font_size;
                $font_size = round($font_size, 1);
			?>
			<?php echo $this->Html->link(
				$tag_name,
				array(
					'controller' => 'events',
					'action' => 'tag',
					'slug' => $tag['id'].'_'.Inflector::slug($tag['name']),
					'direction' => $direction
				),
				array(
					'title' => $tag['count'].' '.__n('event', 'events', $tag['count']),
					'style' => "font-size: {$font_size}%"
				)
			); ?>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<?php if (! empty($tags)): ?>
	<?php foreach ($tags_by_first_letter as $letter => $tags_under_letter): ?>
		<ul id="tag_sublist_<?php echo $letter ?>" class="tag_sublist" style="display: none;">
			<?php foreach ($tags_under_letter as $tag_name => $tag): ?>
				<li>
					<?php echo $this->Html->link(
						ucfirst($tag_name),
						array(
							'controller' => 'events',
							'action' => 'tag',
							'slug' => $tag['id'].'_'.Inflector::slug($tag['name']),
							'direction' => $direction
						)
					); ?>
					<span class="count">
						<?php echo $tag['count']; ?>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endforeach; ?>
<?php endif; ?>

<?php $this->Js->buffer("
	$('#tag_view_options .breakdown a').click(function(event) {
		event.preventDefault();
		var link = $(this);
		var tag_list = link.data('tagList');
		link.parents('ul').find('a.selected').removeClass('selected');
		if (tag_list == 'cloud') {
			$('.tag_sublist:visible').hide();
			$('#tag_index_cloud').show();
			link.addClass('selected');
		} else {
			$('#tag_index_cloud').hide();
			$('.tag_sublist:visible').hide();
			$('#tag_sublist_'+tag_list).show();
			link.addClass('selected');
		}
	});
"); ?>
