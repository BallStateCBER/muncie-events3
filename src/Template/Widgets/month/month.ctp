<table class="calendar" id="calendar_<?= "$year-$month"; ?>" data-year="<?= $year; ?>" data-month="<?= $month; ?>">
    <thead>
        <tr>
            <td class="prev_month">
                <a href="#" class="prev_month" title="Previous month">
                    &larr;
                </a>
            </td>
            <th colspan="5" class="month_name">
                <?= $monthName; ?>
            </th>
            <td class="next_month">
                <a href="#" class="next_month" title="Next month">
                    &rarr;
                </a>
            </td>
        </tr>
        <tr>
            <?php foreach (['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $letter): ?>
                <th class="day_header">
                    <?= $letter; ?>
                </th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php
            for ($cell_num = 0; $cell_num <= 42; $cell_num++) {

                // Beginning of row
                if ($cell_num % 7 == 0) {
                    echo '<tr>';
                }

                // Pre-spacer
                if ($cell_num < $preSpacer) {
                    echo '<td class="spacer">&nbsp;</td>';
                }

                // Calendar date
                if ($cell_num >= $preSpacer && $cell_num < $preSpacer + $lastDay) {
                    $day = $cell_num - $preSpacer + 1;
                    echo ("$year$month$day" == $today) ? '<td class="today">' : '<td>';
                    echo '<div>';
                    //echo '<span class="number">'.$day.'</span>';

                    echo $this->Html->link(
                        $day,
                        [
                            'controller' => 'events',
                            'action' => 'day',
                            $month,
                            $day,
                            $year
                        ],
                        [
                            'class' => 'date',
                            'data-day' => str_pad($day, 2, '0', STR_PAD_LEFT)
                        ]
                    );

                    $date = $year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT).'-'.str_pad($day, 2, '0', STR_PAD_LEFT);
                    if (isset($events[$date]) && !empty($events[$date])) {
                        echo '<ul>';
                        // Testing
                        //$events[$date] = array_merge($events[$date], $events[$date]);
                        //$events[$date] = array_merge($events[$date], $events[$date]);
                        //$events[$date] = array_merge($events[$date], $events[$date]);
                        for ($n = 0; $eventsDisplayedPerDay == 0 || $n < $eventsDisplayedPerDay; $n++) {
                            if (!isset($events[$date][$n])) {
                                break;
                            }
                            $event = $events[$date][$n];
                            echo "<li>";

                            // Event link
                            $link_text = $this->Text->truncate(
                                $event['Event']['title'],
                                50,
                                [
                                    'ending' => '...',
                                    'exact' => false
                                ]
                            );
                            $category_name = $event['Category']['name'];
                            $link_text = $this->Icon->category($category_name).$link_text;
                            echo $this->Html->link(
                                $link_text,
                                [
                                    'controller' => 'events',
                                    'action' => 'view',
                                    'id' => $event['Event']['id']
                                ],
                                [
                                    'escape' => false,
                                    'class' => 'event',
                                    'data-event-id' => $event['Event']['id'],
                                    'title' => $event['Event']['displayed_time'].' - '.$event['Event']['title']
                                ]
                            );

                            echo '</li>';
                        }
                        echo '</ul>';
                        $count = count($events[$date]);
                        if ($eventsDisplayedPerDay > 0 && $count > $eventsDisplayedPerDay) {
                            echo $this->Html->link(
                                $count - $eventsDisplayedPerDay.' more',
                                [
                                    'controller' => 'events',
                                    'action' => 'day',
                                    $month,
                                    $day,
                                    $year
                                ],
                                [
                                    'class' => 'more',
                                    'data-day' => str_pad($day, 2, '0', STR_PAD_LEFT),
                                    'title' => 'View all events on this date'
                                ]
                            );
                        }
                    }
                    echo '</div></td>';
                }

                // After the last day
                if ($cell_num >= $preSpacer + $lastDay - 1) {

                    // End of calendar
                    if ($cell_num % 7 == 6) {
                        echo '</tr>';
                        break;

                    // Normal spacer
                    } else {
                        echo '<td class="spacer">&nbsp;</td>';
                    }
                }

                // End of row
                if ($cell_num % 7 == 6) {
                    echo '</tr>';
                }
            }
        ?>
    </tbody>
</table>

<?= $this->Js->buffer("
    muncieEventsMonthWidget.setCurrentMonth('$month');
    muncieEventsMonthWidget.setCurrentYear('$year');
    muncieEventsMonthWidget.prepareLinks('#calendar_$year-$month');
    var events = ".$this->Js->object($eventsForJson).";
    muncieEventsMonthWidget.setEvents(events);
"); ?>
