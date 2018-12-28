<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User[]|\Cake\Collection\CollectionInterface $users
 */
?>
<h1 class="page_title">
    <?= $titleForLayout; ?>
</h1>
<div class="col-lg-8" id="moderate_events">
    <?php if (empty($users)): ?>
        <p>
            No new users have registered in the last three days. Perhaps it's time for some promo?
        </p>
    <?php else: ?>
        <ul>
            <?php foreach ($users as $user): ?>
                <li>
                    <ul class="actions">
                        <li>
                            <?php
                            $url = ['controller' => 'users', 'action' => 'setUserAsSpam', $user->id];
                            echo $this->Form->postLink(
                                $this->Html->image('icons/cross.png', ['alt' => 'Mark as Spam']).'Mark as Spam',
                                $url,
                                ['escape' => false, 'confirm' => "This user will be marked as spam."],
                                'Are you sure this user is a spam account?'
                            );
                            ?>
                        </li>
                    </ul>
                    <table>
                        <?php if ($user->password == 'spam account'): ?>
                            <tr class="alert alert-danger">
                                <th>
                                    SPAM!
                                </th>
                                <td>
                                    This user has been marked as spam!
                                </td>
                            </tr>
                        <?php endif ?>
                        <tr>
                            <th>
                                Id
                            </th>
                            <td>
                                <?= $user->id ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                Name
                            </th>
                            <td>
                                <?= $user->name ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                Email
                            </th>
                            <td>
                                <?= $user->email ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                Date created
                            </th>
                            <td>
                                <?= date('Y-m-d', strtotime($user->created)) ?>
                            </td>
                        </tr>
                    </table>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>