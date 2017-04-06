<?php
    $logged_in = (boolean) $this->request->session()->read('Auth.User.id');
    $user_role = $this->request->session()->read('Auth.User.role');
    $this->Js->buffer("setupSidebar();");
?>
<div id="sidebar" class="col-md-3">

    <?php if ($logged_in && $user_role == 'admin'): ?>
        <div>
            <h2>Admin</h2>
            <ul class="admin_actions">
                <li>
                    <?php echo $this->Html->link('Approve Events', [
                        'plugin' => false,
                        'controller' => 'events',
                        'action' => 'moderate'
                    ]); ?>
                    <?php if ($unapproved_count): ?>
                        <span class="count">
                            <?php echo $unapproved_count; ?>
                        </span>
                    <?php endif; ?>
                </li>
                <li>
                    <?php echo $this->Html->link('Manage Tags', [
                        'plugin' => false,
                        'controller' => 'tags',
                        'action' => 'manage'
                    ]); ?>
                </li>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isset($header_vars['categories'])): ?>
        <div class="categories">
            <h2>Categories</h2>
            <ul>
                <?php foreach ($header_vars['categories'] as $category): ?>
                    <li>
                        <a href="<?php echo Router::url(array('controller' => 'events', 'action' => 'category', $category['Category']['slug'])); ?>" class="with_icon">
                            <span class="category_name"><?php
                                echo $category['Category']['name'];
                             ?></span>
                            <?php
                                $category_id = $category['Category']['id'];
                                if (isset($sidebar_vars['upcoming_event_totals_by_category'][$category_id])) {
                                    $upcoming_events_count = $sidebar_vars['upcoming_event_totals_by_category'][$category_id];
                                } else {
                                    $upcoming_events_count = 0;
                                }
                                if ($upcoming_events_count):
                                    $title = $upcoming_events_count.' upcoming '.__n('event', 'events', $upcoming_events_count);
                            ?>
                                <span class="upcoming_events_count" title="<?php echo $title; ?>">
                                    <?php echo $upcoming_events_count; ?>
                                </span>
                            <?php endif; ?>
                            <?php echo $this->Icon->category($category['Category']['name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isset($sidebar_vars['locations'])): ?>
        <div class="locations">
            <h2>
                Locations
            </h2>
            <?php if (count($sidebar_vars['locations']) > 0): ?>
                <form id="sidebar_select_location">
                    <select class='form-control'>
                        <option value="">
                            Select a location...
                        </option>
                        <?php foreach ($sidebar_vars['locations'] as $location): ?>
                            <option value="<?php echo $location; ?>">
                                <?php echo $location; ?>
                            </option>
                        <?php endforeach; ?>
                        <option value=""></option>
                        <option value="[past events]">
                            Locations of past events...
                        </option>
                    </select>
                </form>
            <?php else: ?>
                <span class="no_results">
                    No locations found for upcoming events.
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div>
        <h2>
            Tags
            <?php echo $this->Html->link('See all', [
                'controller' => 'tags', 'action' => 'index', 'plugin' => false
            ], ['class' => 'see_all']); ?>
        </h2>
        <?php if (isset($sidebar_vars['upcoming_tags']) && count($sidebar_vars['upcoming_tags']) > 0): ?>
            <?php echo $this->element('tags/cloud', [
                'upcoming_tags' => $sidebar_vars['upcoming_tags'],
                'class' => 'form-control'
            ]); ?>
        <?php else: ?>
            <span class="no_results">
                No tags found for upcoming events.
            </span>
        <?php endif; ?>
    </div>

    <div id="sidebar_mailinglist">
        <h2>
            <?php echo $this->Html->link('Mailing List', [
                'controller' => 'mailing_list', 'action' => 'join', 'plugin' => false
            ]); ?>
        </h2>
        <p>
            <?php echo $this->Html->link('Join the Mailing List', ['plugin' => false, 'controller' => 'mailing_list', 'action' => 'join']); ?>
            and get daily or weekly emails about all upcoming events or only the categories
            that you're interested in.
        </p>
    </div>

    <div id="sidebar_widget">
        <h2>
            <?php echo $this->Html->link('Calendar Widgets', [
                'controller' => 'widgets', 'action' => 'index', 'plugin' => false
            ]); ?>
        </h2>
        <p>
            Join our event promotion network by displaying a free
            <strong>
                <?php echo $this->Html->link('custom calendar widget', ['plugin' => false, 'controller' => 'widgets', 'action' => 'index']); ?>
            </strong>
            on your website.
        </p>
    </div>

</div>
