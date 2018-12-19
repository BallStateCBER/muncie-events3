<?php
/**
 * @var \App\View\AppView $this
 */
?>

<h1 class="page_title">
    <?= $titleForLayout ?>
</h1>

<div id="tag_view_options">
    <table>
        <tr>
            <th>Time</th>
            <td class="direction">
                <?php foreach (['upcoming', 'past'] as $dir): ?>
                    <?= $this->Html->link(
                        ucfirst($dir).' Events',
                        [
                            'controller' => 'tags',
                            'action' => 'index',
                            ($dir == 'upcoming' ? 'future' : 'past')
                        ],
                        [
                            'class' => ($directionAdjective == $dir ? 'selected' : ''),
                        ]
                    ) ?>
                <?php endforeach; ?>
            </td>
        </tr>
        <tr>
            <th>Categories</th>
            <td class="categories">
                <ul>
                    <li>
                        <?= $this->Html->link(
                            'All Categories',
                            [
                                'controller' => 'tags',
                                'action' => 'index',
                                $direction
                            ],
                            [
                                'data-category' => 'all',
                                'class' => ($category == 'all' ? 'selected' : '')
                            ]
                        ) ?>
                    </li>
                    <?php foreach ($categories as $id => $cat): ?>
                        <?php if (in_array($id, $categoriesWithTags)): ?>
                            <li>
                                <?= $this->Html->link(
                                    $this->Icon->category($cat),
                                    [
                                        'controller' => 'tags',
                                        'action' => 'index',
                                        $direction,
                                        $id
                                    ],
                                    [
                                        'title' => $cat,
                                        'class' => ($category == $id ? 'selected' : ''),
                                        'escape' => false
                                    ]
                                ) ?>
                            </li>
                        <?php else: ?>
                            <li class="no_tags">
                                <?= $this->Icon->category($cat) ?>
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
                        <?= $this->Html->link(
                            'All Tags',
                            '#',
                            [
                                'title' => 'View tag cloud',
                                'data-tag-list' => 'cloud',
                                'class' => 'selected'
                            ]
                        ) ?>
                    </li>
                    <?php $letters = array_merge(range('a', 'z'), ['#']); ?>
                    <?php foreach ($letters as $letter): ?>
                        <li>
                            <?php if (isset($tagsByFirstLetter[$letter])): ?>
                                <?= $this->Html->link(
                                    strtoupper($letter),
                                    '#',
                                    [
                                        'title' =>
                                            'View only tags for ' . $directionAdjective .
                                            ' events beginning with ' . strtoupper($letter),
                                        'data-tag-list' => $letter
                                    ]
                                ) ?>
                            <?php else: ?>
                                <span title="No tags for <?= $directionAdjective ?> events beginning with <?= strtoupper($letter) ?>">
                                     <?= strtoupper($letter) ?>
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
            No tags found for any <?= $directionAdjective ?> events.
        </p>
    <?php else: ?>
        <?php
            $minCount = $maxCount = null;
            foreach ($tags as $tagName => $tag) {
                if ($minCount == null) {
                    $minCount = $maxCount = $tag['count'];
                }
                if ($tag['count'] < $minCount) {
                    $minCount = $tag['count'];
                }
                if ($tag['count'] > $maxCount) {
                    $maxCount = $tag['count'];
                }
            }
            $countRange = max($maxCount - $minCount, 1);
            $minFontSize = 75;
            $maxFontSize = 150;
            $fontSizeRange = $maxFontSize - $minFontSize;
        ?>
        <?php foreach ($tags as $tagName => $tag): ?>
            <?php
                $fontSize = log($maxCount) == 0 ? log($tag['count']) / 1 * $fontSizeRange + $minFontSize : log($tag['count']) / log($maxCount) * $fontSizeRange + $minFontSize;
                $fontSize = round($fontSize, 1);
            ?>
            <?php echo $this->Html->link(
                $tagName,
                [
                    'controller' => 'events',
                    'action' => 'tag',
                    'slug' => $tag['id'] . '_' . \Cake\Utility\Text::slug($tag['name']),
                    'direction' => $direction
                ],
                [
                    'title' => $tag['count'] . ' ' . __n('event', 'events', $tag['count']),
                    'style' => "font-size: {$fontSize}%"
                ]
            ); ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if (! empty($tags)): ?>
    <?php foreach ($tagsByFirstLetter as $letter => $tagsUnderLetter): ?>
        <ul id="tag_sublist_<?php echo $letter ?>" class="tag_sublist" style="display: none;">
            <?php foreach ($tagsUnderLetter as $tagName => $tag): ?>
                <li>
                    <?= $this->Html->link(
                        ucfirst($tagName),
                        [
                            'controller' => 'events',
                            'action' => 'tag',
                            'slug' => $tag['id'] . '_' . \Cake\Utility\Text::slug($tag['name']),
                            'direction' => $direction
                        ]
                    ) ?>
                    <span class="count">
                        <?= $tag['count'] ?>
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
