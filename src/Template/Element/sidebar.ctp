<?php
/**
 * @var \App\Model\Entity\Category $category
 * @var \App\View\AppView $this
 * @var array $headerVars
 * @var array $sidebarVars
 * @var int $recentUsersCount
 * @var int $unapprovedCount
 */

$loggedIn = (bool)$this->request->getSession()->read('Auth.User.id');
$userRole = $this->request->getSession()->read('Auth.User.role');
$this->Js->buffer("setupSidebar();");
?>
<div id="sidebar" class="col-lg-3 col-md-4">

    <?php if ($loggedIn && $userRole == 'admin'): ?>
        <div>
            <h2>Admin</h2>
            <ul class="admin_actions">
                <li>
                    <?= $this->Html->link('Approve Events', [
                        'plugin' => false,
                        'prefix' => 'admin',
                        'controller' => 'events',
                        'action' => 'moderate'
                    ]) ?>
                    <?php if ($unapprovedCount): ?>
                        <span class="count">
                            <?= $unapprovedCount ?>
                        </span>
                    <?php endif; ?>
                </li>
                <li>
                    <?= $this->Html->link('Moderate New Users', [
                        'plugin' => false,
                        'prefix' => 'admin',
                        'controller' => 'users',
                        'action' => 'moderate'
                    ]) ?>
                    <?php if ($recentUsersCount): ?>
                        <span class="count">
                            <?= $recentUsersCount ?>
                        </span>
                    <?php endif; ?>
                </li>
                <li>
                    <?= $this->Html->link('Manage Tags', [
                        'plugin' => false,
                        'prefix' => 'admin',
                        'controller' => 'tags',
                        'action' => 'manage'
                    ]) ?>
                </li>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isset($sidebarVars['categories'])): ?>
        <div class="categories">
            <h2>Categories</h2>
            <ul>
                <?php foreach ($sidebarVars['categories'] as $category): ?>
                    <li>
                        <a href="<?= $category['url'] ?>" class="with_icon">
                            <span class="category_name"><?= $category['name'] ?></span>
                            <?php if ($category['upcomingEventsCount']): ?>
                                <span class="upcoming_events_count" title="<?= $category['upcomingEventsTitle'] ?>">
                                    <?= $category['upcomingEventsCount'] ?>
                                </span>
                            <?php endif; ?>
                            <?= $this->Icon->category($category['name']) ?>
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
                    <label class="sr-only" for="sidebar-locations">
                        Select a location
                    </label>
                    <select class='form-control' name="locations" id="sidebar-locations">
                        <option value="">
                            Select a location...
                        </option>
                        <?php foreach ($sidebarVars['locations'] as $location => $slug): ?>
                            <option value="<?= $slug ?>">
                                <?= $location ?>
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
            <?= $this->Html->link(
                'See all',
                [
                    'plugin' => false,
                    'prefix' => false,
                    'controller' => 'Tags',
                    'action' => 'index'
                ],
                ['class' => 'see_all'])
            ?>
        </h2>
        <?php if (isset($sidebarVars['upcomingTags']) && count($sidebarVars['upcomingTags']) > 0): ?>
            <?= $this->element('tags/cloud', [
                'upcomingTags' => $sidebarVars['upcomingTags'],
                'class' => 'form-control'
            ]) ?>
        <?php else: ?>
            <span class="no_results">
                No tags found for upcoming events.
            </span>
        <?php endif; ?>
    </div>

    <div id="sidebar_mailinglist">
        <h2>
            Mailing List
        </h2>
        <p>
            <?= $this->Html->link('Join the Mailing List', ['plugin' => false, 'prefix' => false, 'controller' => 'mailing_list', 'action' => 'join']) ?>
            and get daily or weekly emails about all upcoming events or only the categories
            that you're interested in.
        </p>
    </div>

    <div id="sidebar_widget">
        <h2>
            Calendar Widgets
        </h2>
        <p>
            Join our event promotion network by displaying a free
            <strong>
                <?= $this->Html->link('custom calendar widget', ['plugin' => false, 'prefix' => false, 'controller' => 'widgets', 'action' => 'index']) ?>
            </strong>
            on your website.
        </p>
    </div>
</div>
