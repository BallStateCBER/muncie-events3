<table class="calendar" id="calendar_<?= "$year-$month"; ?>" data-year="<?= $year; ?>" data-month="<?= $month; ?>">
     <thead>
          <tr>
               <td class="prev_month">
                    <!--a href="#" class="prev_month" title="Previous month">
                         &larr;
                    </a-->
                    <?php echo $this->Calendar->prevMonth($month, $year); ?>
               </td>
               <th colspan="5" class="month_name">
                    <?= $monthName; ?>
               </th>
               <td class="next_month">
                    <!--a href="#" class="next_month" title="Next month">
                         &rarr;
                    </a-->
                    <?php echo $this->Calendar->nextMonth($month, $year); ?>
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
            for ($cellNum = 0; $cellNum <= 42; $cellNum++) {

                // Beginning of row
                if ($cellNum % 7 == 0) {
                    echo '<tr>';
                }

                // Pre-spacer
                if ($cellNum < $preSpacer) {
                    echo '<td class="spacer">&nbsp;</td>';
                }

                // Calendar date
                if ($cellNum >= $preSpacer && $cellNum < $preSpacer + $lastDay) {
                    $day = $cellNum - $preSpacer + 1;
                    echo ("$year$month$day" == $today) ? '<td class="today">' : '<td>';
                    echo '<div>';

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
                        if (!isset($events[$date][0])) {
                            echo $this->element('widgets/month_event', [
                                'event' => $events[$date]
                            ]);
                        }
                        for ($n = 0; $eventsDisplayedPerDay == 0 || $n < $eventsDisplayedPerDay; $n++) {
                            if (isset($events[$date][$n])) {
                                echo $this->element('widgets/month_event', [
                                    'event' => $events[$date][$n]
                                ]);
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
                        }
                    }
                    echo '</ul>';
                }
                echo '</div></td>';
            // After the last day
            if ($cellNum >= $preSpacer + $lastDay - 1) {

                // End of calendar
                if ($cellNum % 7 == 6) {
                    echo '</tr>';
                    break;

                // Normal spacer
                } else {
                    echo '<td class="spacer">&nbsp;</td>';
                }
            }

            // End of row
            if ($cellNum % 7 == 6) {
                echo '</tr>';
            }
            }
        ?>
     </tbody>
</table>

<?php $this->Js->buffer("
     muncieEventsMonthWidget.setCurrentMonth('$month');
     muncieEventsMonthWidget.setCurrentYear('$year');
     muncieEventsMonthWidget.prepareLinks('#calendar_$year-$month');
     var events = ".$this->Js->object($eventsForJson).";
     muncieEventsMonthWidget.setEvents(events);
"); ?>
