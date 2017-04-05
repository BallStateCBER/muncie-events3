<div class="event">
    <h1 class="title">
        <?php echo $event['Event']['title']; ?>
    </h1>
    <?php
        echo $this->element('events/actions', array('event' => $event, 'can_edit' => false));
        $this->Js->buffer("setupEventActions('.event');");
    ?>
    <div class="header_details">
        <table class="details">
            <tr>
                <th>When</th>
                <td>
                    <?php echo $this->Calendar->date($event); ?>
                    <br />
                    <?php echo $this->Calendar->time($event); ?>
                </td>
            </tr>
            <tr>
                <th>Where</th>
                <td>
                    <?php echo $event['Event']['location']; ?>
                    <?php if ($event['Event']['location_details']): ?>
                        <br />
                        <?php echo $event['Event']['location_details']; ?>
                    <?php endif; ?>
                    <?php if ($event['Event']['address']): ?>
                        <br />
                        <?php echo $event['Event']['address']; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>What</th>
                <td class="what">
                    <?php
                        echo $this->Icon->category($event['Category']['name']).$event['Category']['name'];
                        if (!empty($event['Tag'])) {
                            echo ': <span class="tags">';
                            $linked_tags = array();
                            foreach ($event['Tag'] as $tag) {
                                $linked_tags[] = $tag['name'];
                                /*
                                $linked_tags[] = $this->Html->link($tag['name'], array(
                                    'controller' => 'events',
                                    'action' => 'index',
                                    'tag' => $tag['id'].'_'.Inflector::slug($tag['name'])
                                ));
                                */
                            }
                            echo implode(', ', $linked_tags);
                            echo '</div>';
                        }
                    ?>
                </td>
            </tr>
            <?php if ($event['Event']['cost']): ?>
                <tr>
                    <th>Cost</th>
                    <td><?php echo $event['Event']['cost']; ?></td>
                </tr>
            <?php endif; ?>
            <?php if ($event['Event']['age_restriction']): ?>
                <tr>
                    <th>Ages</th>
                    <td><?php echo $event['Event']['age_restriction']; ?></td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($event['EventsImage'])): ?>
                <tr>
                    <th>Images</th>
                    <td>
                        <?php foreach ($event['EventsImage'] as $image): ?>
                            <?php echo $this->Calendar->thumbnail('tiny', array(
                                'filename' => $image['Image']['filename'],
                                'caption' => $image['caption'],
                                'group' => 'event_view'.$event['Event']['id']
                            )); ?>
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
    <div class="description">
        <?php echo $this->Text->autolink($event['Event']['description'], array(
            'escape' => false
        )); ?>
    </div>
    <div class="footer">
        <?php
            $url = Router::url(array(
                'controller' => 'events',
                'action' => 'view',
                'id' => $event['Event']['id']
            ), true);
            echo $this->Html->link('Go to event page', $url);
        ?>
        <?php if ($event['Event']['source']): ?>
            <br />
            Source:
            <?php echo $this->Text->autoLink($event['Event']['source']); ?>
        <?php endif; ?>
    </div>
</div>
