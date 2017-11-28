<?php

use Cake\Routing\Router;

$userId = $this->request->session()->read('Auth.User.id');
    $userRole = $this->request->session()->read('Auth.User.role') ?: null;
    $canEdit = $userId && ($userRole == 'admin' || $userId == $event['user_id']);

    $eventUrl = Router::url([
        'controller' => 'events',
        'action' => 'view',
        'id' => $event['id']
    ], true);
?>
<div class="actions">
    <!--?= $this->Facebook->likeButton([
        'href' => $eventUrl,
        'show_faces' => false,
        'layout' => 'button_count',
        'app_id' => '496726620385625'
    ]); ?-->
    <div class="export_options_container">
        <?php echo $this->Html->link(
            $this->Html->image('/img/icons/calendar--arrow.png').'Export',
            "#" . $event['id'] . "_options",
            [
                'escape' => false,
                'title' => 'Export to another calendar application',
                'id' => 'export_event_'.$event['id'],
                'class' => 'export_options_toggler',
                'aria-expanded' => 'false',
                'aria-controls' => $event['id'].'_options',
                'data-toggle' => 'collapse',
                'data-target' => '#'.$event['id'].'_options'
            ]
        ); ?>
        <div class="export_options collapse" id="<?= $event['id'] ?>_options">
            <?php echo $this->Html->link(
                'iCal',
                [
                    'controller' => 'events',
                    'action' => 'ics',
                    $event['id']
                ],
                [
                    'title' => 'Download iCalendar (.ICS) file'
                ]
            ); ?>
            <?php
                $date = strtotime($event->start->i18nFormat('yyyyMMddHHmmss'));

                // Determine UTC "YYYYMMDDTHHMMSS" start/end values
                $start = date('Ymd', $date).'T'.date('His', $date).'Z';

                $endStamp = $date;
                if ($event->end) {
                    $endTime = strtotime($event->end->i18nFormat('yyyyMMddHHmmss'));
                    $endStamp = $endTime;
                }
                $end = date('Ymd', $date).'T'.date('His', $endStamp).'Z';

                // Clean up and truncate description
                $description = $event['description'];
                $description = strip_tags($description);
                $description = str_replace('&nbsp;', '', $description);
                $description = $this->Text->truncate(
                    $description,
                    1000,
                    [
                        'ellipsis' => "... (continued at $eventUrl)",
                        'exact' => false
                    ]
                );

                /* In parentheses after the location name, the address has
                 * 'Muncie, IN' tacked onto the end if 'Muncie' is not
                 * mentioned in it. */
                $address = trim($event['address']);
                if ($address == '') {
                    $address = 'Muncie, IN';
                } elseif (stripos($address, 'Muncie') === false) {
                    $address .= ', Muncie, IN';
                }
                $location = $event['location'];
                if ($event['location_details']) {
                    $location .= ', '.$event['location_details'];
                }
                $location .= ' ('.$address.')';

                $google_cal_url = 'http://www.google.com/calendar/event?action=TEMPLATE';
                $google_cal_url .= '&text='.urlencode($event['title']);
                $google_cal_url .= '&dates='.$start.'/'.$end;
                $google_cal_url .= '&details='.urlencode($description);
                $google_cal_url .= '&location='.urlencode($location);
                $google_cal_url .= '&trp=false';
                $google_cal_url .= '&sprop=Muncie%20Events';
                $google_cal_url .= '&sprop=name:http%3A%2F%2Fmuncieevents.com';

                echo $this->Html->link(
                    'Google',
                    $google_cal_url,
                    [
                        'title' => 'Add to Google Calendar'
                    ]
                );
            ?>
            <?php echo $this->Html->link(
                'Outlook',
                [
                    'controller' => 'events',
                    'action' => 'ics',
                    $event['id']
                ],
                [
                    'title' => 'Add to Microsoft Outlook'
                ]
            ); ?>
        </div>
    </div>
    <?php if ($userRole == 'admin' && !$event['approved_by']): ?>
        <?php echo $this->Html->link(
            $this->Html->image('/img/icons/tick.png').'Approve',
            [
                'controller' => 'events',
                'action' => 'approve',
                'id' => $event['id']
            ],
            ['escape' => false]
        ); ?>
    <?php endif; ?>
    <?php if ($canEdit): ?>
        <?php echo $this->Html->link(
            $this->Html->image('/img/icons/pencil.png').'Edit',
            [
                'controller' => 'events',
                'action' => 'edit',
                'id' => $event['id']
            ],
            ['escape' => false]
        ); ?>
        <?php echo $this->Form->postLink(
            $this->Html->image('/img/icons/cross.png').'Delete',
            [
                'controller' => 'events',
                'action' => 'delete',
                'id' => $event['id']
            ],
            ['escape' => false],
            'Are you sure that you want to delete this event?'
        ); ?>
    <?php endif; ?>
</div>
