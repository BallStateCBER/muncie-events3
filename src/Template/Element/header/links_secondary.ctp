    <?php if ($this->request->session()->read('Auth.User')): ?>
        <li class="nav-item">
            <?php #if ($facebook_user):?>
                <?php #echo $this->Facebook->disconnect([
                    #'redirect' => ['controller' => 'Users', 'action' => 'logout'],
                    #'label' => 'Logout'
            #    ]);?>
            <?php #else:?>
                <?= $this->Html->link('Log out', ['plugin' => false, 'controller' => 'Users', 'action' => 'logout'], ['class'=>'nav-link']); ?>
            <?php #endif;?>
        </li>
        <li class="<?php echo (!empty($this->request->params['controller']=='Users') && ($this->request->params['action']=='account'))?'active ' :'' ?>nav-item">
            <?= $this->Html->link('Account', ['plugin' => false, 'controller' => 'Users', 'action' => 'account'], ['class'=>'nav-link']); ?>
        </li>
    <?php else: ?>
        <li class="<?php echo (($this->request->params['controller']=='Users') && ($this->request->params['action']=='login'))?'active ' :'' ?>nav-item">
            <?= $this->Html->link('Log in', ['plugin' => false, 'controller' => 'Users', 'action' => 'login'], ['class'=>'nav-link']); ?>
        </li>
        <li class="<?php echo (($this->request->params['controller']=='Users') && ($this->request->params['action']=='register'))?'active ' :'' ?>nav-item">
            <?= $this->Html->link('Register', ['plugin' => false, 'controller' => 'Users', 'action' => 'register'], ['class'=>'nav-link']); ?>
        </li>
    <?php endif; ?>
    <li class="<?php echo (($this->request->params['controller']=='Pages') && ($this->request->params['action']=='contact'))?'active ' :'' ?>nav-item">
        <?= $this->Html->link(__('Contact'), ['controller' => 'Pages', 'action' => 'contact'], ['class' => 'nav-link']); ?>
    </li>
    <li class="<?php echo (($this->request->params['controller']=='Pages') && ($this->request->params['action']=='about'))?'active ' :'' ?>nav-item">
        <?= $this->Html->link(__('About'), ['controller' => 'Pages', 'action' => 'about'], ['class' => 'nav-link']); ?>
    </li>
