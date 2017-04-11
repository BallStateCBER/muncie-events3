<h1 class="page_title">
    <?php echo $titleForLayout; ?>
</h1>

<?php if (empty($pastLocations)): ?>
    <p class="alert alert-info">
        No locations found for past events.
    </p>
<?php else: ?>
    <ul>
        <?php foreach ($pastLocations as $pastLocation): ?>
            <li>
                <?php echo $this->Html->link($pastLocation, [
                    'controller' => 'events',
                    'action' => 'location',
                    $pastLocation,
                    'past'
                ]); ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
