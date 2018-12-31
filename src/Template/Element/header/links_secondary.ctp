<?php
/**
 * @var \App\View\AppView $this
 */
?>
<?php if ($this->request->getSession()->read('Auth.User')): ?>
    <li class="nav-item">
        <?php #if ($facebook_user):?>
            <?php #echo $this->Facebook->disconnect([
                #'redirect' => ['controller' => 'Users', 'action' => 'logout'],
                #'label' => 'Logout'
        #    ]);?>
        <?php #else:?>
            <?= $this->Html->link('Log out', ['plugin' => false, 'prefix' => false, 'controller' => 'Users', 'action' => 'logout'], ['class'=>'nav-link']) ?>
        <?php #endif;?>
    </li>
    <li class="<?= (!empty($this->request->getParam('controller') == 'Users') && ($this->request->getParam('action') == 'account')) ? 'active ' : '' ?>nav-item">
        <?= $this->Html->link('Account', ['plugin' => false, 'prefix' => false, 'controller' => 'Users', 'action' => 'account'], ['class'=>'nav-link']) ?>
    </li>
<?php else: ?>
    <li class="<?= (($this->request->getParam('controller') == 'Users') && ($this->request->getParam('action') == 'login')) ? 'active ' : '' ?>nav-item">
        <?= $this->Html->link('Log in', ['plugin' => false, 'prefix' => false, 'controller' => 'Users', 'action' => 'login'], ['class'=>'nav-link']) ?>
    </li>
    <li class="<?= (($this->request->getParam('controller') == 'Users') && ($this->request->getParam('action') == 'register')) ? 'active ' : '' ?>nav-item">
        <?= $this->Html->link('Register', ['plugin' => false, 'prefix' => false, 'controller' => 'Users', 'action' => 'register'], ['class'=>'nav-link']) ?>
    </li>
<?php endif; ?>
<li class="<?= (($this->request->getParam('controller') == 'Pages') && ($this->request->getParam('action') == 'contact')) ? 'active ' : '' ?>nav-item">
    <?= $this->Html->link('Contact', ['plugin' => false, 'prefix' => false, 'controller' => 'Pages', 'action' => 'contact'], ['class' => 'nav-link']) ?>
</li>
<li class="<?= (($this->request->getParam('controller') == 'Pages') && ($this->request->getParam('action') == 'about')) ? 'active ' : '' ?>nav-item">
    <?= $this->Html->link('About Muncie Events', ['plugin' => false, 'prefix' => false, 'controller' => 'Pages', 'action' => 'about'], ['class' => 'nav-link']) ?>
</li>
