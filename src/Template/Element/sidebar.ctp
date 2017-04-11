<?php
use Cake\Routing\Router;

$loggedIn = (boolean) $this->request->session()->read('Auth.User.id');
    $userRole = $this->request->session()->read('Auth.User.role');
    $this->Js->buffer("setupSidebar();");
?>
<div id="sidebar" class="col-md-3">

    <?php if ($loggedIn && $userRole == 'admin'): ?>
        <div>
            <h2>Admin</h2>
            <ul class="admin_actions">
                <li>
                    <?= $this->Html->link('Approve Events', [
                        'plugin' => false,
                        'controller' => 'events',
                        'action' => 'moderate'
                    ]); ?>
                    <?php if ($unapprovedCount): ?>
                        <span class="count">
                            <?= $unapprovedCount; ?>
                        </span>
                    <?php endif; ?>
                </li>
                <li>
                    <?= $this->Html->link('Manage Tags', [
                        'plugin' => false,
                        'controller' => 'tags',
                        'action' => 'manage'
                    ]); ?>
                </li>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isset($headerVars['categories'])): ?>
        <div class="categories">
            <h2>Categories</h2>
            <ul>
                <?php foreach ($headerVars['categories'] as $category): ?>
                    <li>
                        <a href="<?= Router::url(['controller' => 'events', 'action' => 'category', $category->slug]); ?>" class="with_icon">
                            <span class="category_name"><?php
                                echo $category->name;
                             ?></span>
                            <?php
                                $categoryId = $category->id;
                                if (isset($sidebarVars['upcomingEventsByCategory'][$categoryId])) {
                                    $upcomingEventsCount = $sidebarVars['upcomingEventsByCategory'][$categoryId];
                                } else {
                                    $upcomingEventsCount = 0;
                                }
                                if ($upcomingEventsCount):
                                    $title = $upcomingEventsCount.' upcoming '.__n('event', 'events', $upcomingEventsCount);
                            ?>
                                <span class="upcoming_events_count" title="<?= $title; ?>">
                                    <?= $upcomingEventsCount; ?>
                                </span>
                            <?php endif; ?>
                            <?= $this->Icon->category($category->name); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isset($sidebarVars['locations'])): ?>
        <div class="locations">
            <h2>
                Locations
            </h2>
            <?php if (count($sidebarVars['locations']) > 0): ?>
                <form id="sidebar_select_location">
                    <select class='form-control'>
                        <option value="">
                            Select a location...
                        </option>
                        <?php foreach ($sidebarVars['locations'] as $location): ?>
                            <option value="<?= $location; ?>">
                                <?= $location; ?>
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
            <?= $this->Html->link('See all', [
                'controller' => 'tags', 'action' => 'index', 'plugin' => false
            ], ['class' => 'see_all']); ?>
        </h2>
        <?php if (isset($sidebarVars['upcomingTags']) && count($sidebarVars['upcomingTags']) > 0): ?>
            <?= $this->element('tags/cloud', [
                'upcomingTags' => $sidebarVars['upcomingTags'],
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
            <?= $this->Html->link('Mailing List', [
                'controller' => 'mailing_list', 'action' => 'join', 'plugin' => false
            ]); ?>
        </h2>
        <p>
            <?= $this->Html->link('Join the Mailing List', ['plugin' => false, 'controller' => 'mailing_list', 'action' => 'join']); ?>
            and get daily or weekly emails about all upcoming events or only the categories
            that you're interested in.
        </p>
    </div>

    <div id="sidebar_widget">
        <h2>
            <?= $this->Html->link('Calendar Widgets', [
                'controller' => 'widgets', 'action' => 'index', 'plugin' => false
            ]); ?>
        </h2>
        <p>
            Join our event promotion network by displaying a free
            <strong>
                <?= $this->Html->link('custom calendar widget', ['plugin' => false, 'controller' => 'widgets', 'action' => 'index']); ?>
            </strong>
            on your website.
        </p>
    </div>

</div>
