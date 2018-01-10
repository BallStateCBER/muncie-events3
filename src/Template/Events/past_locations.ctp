<?php

use Cake\Utility\Inflector;

?>

<h1 class="page_title">
    <?php echo $titleForLayout; ?>
</h1>

<div id="tag_view_options">
    <table>
        <tr>
            <th>Breakdown</th>
            <td class="breakdown">
                <ul>
                    <li>
                        <?php echo $this->Html->link(
                            'All Locations',
                            '#',
                            [
                                'title' => 'View tag cloud',
                                'data-tag-list' => 'cloud',
                                'class' => 'selected'
                            ]
                        ); ?>
                    </li>
                    <?php $letters = array_merge(range('a', 'z'), ['#']); ?>
                    <?php foreach ($letters as $letter): ?>
                        <li>
                            <?php echo $this->Html->link(
                                strtoupper($letter),
                                '#',
                                [
                                    'title' => 'View only locations beginning with '.strtoupper($letter),
                                    'data-tag-list' => $letter
                                ]
                            ); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </td>
        </tr>
    </table>
</div>

<div id="tag_index_cloud" class="loc_cloud">
    <?php if (empty($pastLocations)): ?>
        <p class="alert alert-info">
            No locations found for past events.
        </p>
    <?php else: ?>
        <ul>
            <?php foreach ($pastLocations as $location => $slug): ?>
                <li>
                    <?php echo $this->Html->link($location, [
                        'controller' => 'events',
                        'action' => 'location',
                        $slug,
                        'past'
                    ]); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php foreach ($locsByFirstLetter as $letter => $locsUnderLetter): ?>
    <ul id="tag_sublist_<?php echo $letter ?>" class="tag_sublist" style="display: none;">
        <?php if (empty($locsUnderLetter)): ?>
            Sorry. No locations have been set that begin with the letter "<?= strtoupper($letter) ?>"!
        <?php else: ?>
            <?php foreach ($locsUnderLetter as $slug => $loc): ?>
                <li>
                    <?php echo $this->Html->link($loc, [
                        'controller' => 'events',
                        'action' => 'location',
                        $slug,
                        'past'
                    ]); ?>
                </li>
            <?php endforeach ?>
        <?php endif ?>
    </ul>
<?php endforeach; ?>

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
