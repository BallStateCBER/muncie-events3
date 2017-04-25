<ul class="navbar-nav">
    <li class="<?php echo (($this->request->params['controller']=='Events') && ($this->request->params['action']=='index'))?'active ' :'' ?>nav-item">
        <?= $this->Html->link(__('Home'), ['controller' => 'Events', 'action' => 'index'], ['class' => 'nav-link']); ?>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="date_picker_toggler" data-toggle="collapse" href="#header_nav_datepicker" aria-expanded="false" aria-controls="header_nav_datepicker">Go to Date...</a>
        <?php
            if (!isset($default)) {
                $default = date('m/d/Y');
            }
        ?>
        <div id="header_nav_datepicker" class="collapse" aria-labelledby="date_picker_toggler">
            <div>
                <?php
                $dayLinks = [];
                    foreach ($headerVars['populatedDates'] as $date) {
                        $dayLinks[] = $this->Html->link($date[0].', '.$date[1].' '.$date[3], [
                            'controller' => 'events',
                            'action' => 'day',
                            $date[2],
                            $date[3],
                            $date[4]
                        ]);
                        if (count($dayLinks) == 7) {
                            break;
                        }
                    }
                ?>
                <?php if (!empty($dayLinks)): ?>
                    <ul>
                        <?php foreach ($dayLinks as $dayLink): ?>
                            <li class="nav-item">
                                <?php echo $dayLink; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <div id="header_datepicker"></div>
            </div>
        </div>
    </li>
    <li class="<?php echo (($this->request->params['controller']=='Events') && ($this->request->params['action']=='add'))?'active ' :'' ?>nav-item">
        <?= $this->Html->link(__('Add Event'), ['controller' => 'Events', 'action' => 'add'], ['class' => 'nav-link']); ?>
    </li>
    <li class="<?php echo (($this->request->params['controller']=='Widgets') && ($this->request->params['action']=='index'))?'active ' :'' ?>nav-item">
        <?= $this->Html->link(__('Widgets'), ['controller' => 'Widgets', 'action' => 'index'], ['class' => 'nav-link']); ?>
    </li>
</ul>
<?php
    if (isset($header_vars['populatedDates'])) {
        foreach ($header_vars['populatedDates'] as $month => $days) {
            $quoted_days = [];
            foreach ($days as $day) {
                $quoted_days[] = "'$day'";
            }
            $this->Js->buffer("muncieEvents.populatedDates['$month'] = [" . implode(',', $quoted_days) . "];");
        }
    }
    $this->Js->buffer("setupHeaderNav();");
?>
