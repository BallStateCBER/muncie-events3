<?php

use Cake\Routing\Router;

if (!isset($canEdit)) {
    $userId = $this->request->session()->read('Auth.User.id');
    $user_role = $this->request->session()->read('Auth.User.role');
    $canEdit = $userId && ($user_role == 'admin' || $userId == $event['user_id']);
}
    $eventUrl = Router::url([
        'controller' => 'events',
        'action' => 'view',
        'id' => $event['id']
    ], true);
?>
<div class="actions">
    <?php /* echo $this->Facebook->like([
        'href' => $eventUrl,
        'show_faces' => false,
        'layout' => 'button_count',
        'app_id' => '496726620385625'
    ]); */ ?>
    <div class="export_options_container">
        <?php echo $this->Html->link(
            $this->Html->image('/img/icons/calendar--arrow.png').'Export',
            '#',
            [
                'escape' => false,
                'title' => 'Export to another calendar application',
                'id' => 'export_event_'.$event['id'],
                'class' => 'export_options_toggler'
            ]
        ); ?>
        <div class="export_options" style="display: none;">
            <?php echo $this->Html->link(
                'iCal',
                [
                    'controller' => 'events',
                    'action' => 'view',
                    'id' => $event['id'],
                    'ext' => 'ics'
                ],
                [
                    'title' => 'Download iCalendar (.ICS) file'
                ]
            ); ?>
            <?php
                $date = strtotime($event->date->i18nFormat('yyyyMMddHHmmss'));
                $startTime = strtotime($event->time_start->i18nFormat('yyyyMMddHHmmss'));

                // Determine UTC "YYYYMMDDTHHMMSS" start/end values
                $start_est = date('Ymd', $date).'T'.date('His', $startTime);
                $start_utc = gmdate('Ymd', $date).'T'.gmdate('His', $startTime).'Z';

                $endStamp = $startTime;
                if ($event->time_end) {
                    $endTime = strtotime($event->time_end->i18nFormat('yyyyMMddHHmmss'));
                    $endStamp = $endTime;
                }
                $end_est = date('Ymd', $date).'T'.date('His', $endStamp);
                $end_utc = gmdate('Ymd', $date).'T'.gmdate('His', $endStamp).'Z';

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
                $google_cal_url .= '&dates='.$start_utc.'/'.$end_utc;
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
                    'action' => 'view',
                    'id' => $event['id'],
                    'ext' => 'ics'
                ],
                [
                    'title' => 'Add to Microsoft Outlook'
                ]
            ); ?>
            <?php
                $location = $event['location'];
                if ($event['location_details']) {
                    $location .= ', '.$event['location_details'];
                }
                $yahoo_cal_url = 'http://calendar.yahoo.com/?';
                $yahoo_cal_url .= 'in_loc='.urlencode($location);
                $yahoo_cal_url .= '&in_st='.urlencode($event['address']);
                $yahoo_cal_url .= '&in_csz='.urlencode('Muncie, IN');
                $yahoo_cal_url .= '&TITLE='.urlencode($event['title']);
                $yahoo_cal_url .= '&URL='.urlencode($eventUrl);
                $yahoo_cal_url .= '&ST='.$start_est;
                if ($start_est != $end_est) {
                    $yahoo_cal_url .= '&ET='.$end_est;
                }
                $yahoo_cal_url .= '&DESC='.urlencode($description);
                $yahoo_cal_url .= '&v=60';
                echo $this->Html->link(
                    'Yahoo!',
                    $yahoo_cal_url,
                    [
                        'title' => 'Add to Yahoo!Calendar'
                    ]
                );
            ?>
        </div>
    </div>
    <?php if ($user_role == 'admin' && !$event['approved_by']): ?>
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
