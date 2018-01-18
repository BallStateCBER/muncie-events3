<?php
    use Cake\Core\Configure;

    if (!empty($flashMessage)) {
        foreach ($flashMessage as $msg) {
            $formattedMsg = str_replace('"', '\"', $msg['message']);
            $formattedMsg = str_replace("\n", "\\n", $formattedMsg);
            $this->Js->buffer('insertFlashMessage("' . $formattedMsg . '", "' . $msg['class'] . '");');
        }
    }

    $googleAnalyticsId = Configure::read('google_analytics_id');
    $debug = Configure::read('debug');
    $gaConfig = [
        'page_location' => $this->request->getUri()->__toString(),
        'page_path' => $this->request->getUri()->getPath()
    ];
    if (isset($titleForLayout) && $titleForLayout) {
        $gaConfig['page_title'] = $titleForLayout;
    }
?>

<?php if ($googleAnalyticsId && !$debug): ?>
    <script>
        gtag('config', '<?= $googleAnalyticsId ?>', <?= json_encode($gaConfig) ?>);
        gtag('event', 'page_view');
    </script>
<?php endif; ?>

<?= $this->fetch('content') ?>
<?= $this->Js->writeBuffer() ?>
