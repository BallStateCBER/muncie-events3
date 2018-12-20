<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="tag_cloud">
    <?php if (empty($upcomingTags)): ?>
        <p class="empty">
            No tags found.
        </p>
    <?php else: ?>
        <?php
            $min_count = $max_count = null;
            foreach ($upcomingTags as $tag_info) {
                if ($min_count == null) {
                    $min_count = $max_count = $tag_info['count'];
                }
                if ($tag_info['count'] < $min_count) {
                    $min_count = $tag_info['count'];
                }
                if ($tag_info['count'] > $max_count) {
                    $max_count = $tag_info['count'];
                }
            }
            $count_range = max($max_count - $min_count, 1);
            $min_font_size = 75;
            $max_font_size = 150;
            $font_size_range = $max_font_size - $min_font_size;
        ?>
        <ul class="list-group">
            <?php foreach ($upcomingTags as $tag_info): ?>
                <?php
                    $font_size = $min_font_size + round(
                        $font_size_range * (($tag_info['count'] - $min_count) / $count_range)
                    );
                    echo $this->Html->link(
                        '<li class="list-group-item" style="font-size: ' . $font_size . '%;">' .
                            $tag_info['name'] .
                        '</li>',
                        [
                            'controller' => 'events',
                            'action' => 'tag',
                            'slug' => $tag_info['id'] . '_' . Text::slug($tag_info['name'])
                        ],
                        [
                            'escape' => false,
                            'id' => 'filter_tag_' . $tag_info['id']
                        ]
                    );
                ?>
                <?php // $this->Js->buffer("setTagFilterListener('filter_tag_{$tag_info['id']}', '{$tag_info['id']}_".Inflector::slug($tag_name)."');");?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
