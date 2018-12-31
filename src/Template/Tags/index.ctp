<?php
/**
 * @var \App\View\AppView $this
 * @var array $tagsByFirstLetter
 * @var callable $calculateFontSize
 * @var int[] $categoriesWithTags
 * @var string $category
 * @var string $directionAdjective
 * @var string[] $categories
 */

use Cake\Utility\Text; ?>

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
                    <?php foreach ($letters as $letter): ?>
                        <li>
                            <?php if (isset($tagsByFirstLetter[$letter])): ?>
                                <?php
                                    $label = $letter == 'nonalpha' ? '#' : strtoupper($letter);
                                    $title = "View only tags for $directionAdjective events beginning with ";
                                    $title .= $letter == 'nonalpha' ? 'numbers or symbols' : strtoupper($letter);
                                    echo $this->Html->link(
                                        $label,
                                        '#',
                                        [
                                            'title' => $title,
                                            'data-tag-list' => $letter
                                        ]
                                    );
                                ?>
                            <?php else: ?>
                                <?php
                                    $title = sprintf(
                                        'No tags for %s events beginning with %s',
                                        $directionAdjective,
                                        strtoupper($letter)
                                    );
                                ?>
                                <span title="<?= $title ?>">
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
        <?php foreach ($tags as $tagName => $tag): ?>
            <?= $this->Html->link(
                $tagName,
                [
                    'controller' => 'events',
                    'action' => 'tag',
                    'slug' => sprintf(
                        '%s_%s',
                        $tag['id'],
                        Text::slug($tag['name'])
                    ),
                    'direction' => $direction
                ],
                [
                    'title' => sprintf(
                        '%s %s',
                        $tag['count'],
                        __n('event', 'events', $tag['count'])
                    ),
                    'style' => 'font-size: ' . $calculateFontSize($tag['count']) . '%'
                ]
            ) ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if (! empty($tags)): ?>
    <?php foreach ($tagsByFirstLetter as $letter => $tagsUnderLetter): ?>
        <ul id="tag_sublist_<?= $letter ?>" class="tag_sublist" style="display: none;">
            <?php foreach ($tagsUnderLetter as $tagName => $tag): ?>
                <li>
                    <?= $this->Html->link(
                        ucfirst($tagName),
                        [
                            'controller' => 'events',
                            'action' => 'tag',
                            'slug' => sprintf(
                                '%s_%s',
                                $tag['id'],
                                Text::slug($tag['name'])
                            ),
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
