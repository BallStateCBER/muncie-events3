<?php
    use Cake\Core\Configure;

    if (!empty($flashMessage)) {
        foreach ($flashMessage as $msg) {
            $formattedMsg = str_replace('"', '\"', $msg['message']);
            $formattedMsg = str_replace("\n", "\\n", $formattedMsg);
            $this->Js->buffer('insertFlashMessage("' . $formattedMsg . '", "' . $msg['class'] . '");');
        }
    }

    // Only invoke Google Analytics if an ID is found and the page is not being served from the development server
    $google_analytics_id = Configure::read('google_analytics_id');
    $not_localhost = isset($_SERVER['SERVER_NAME']) && mb_stripos($_SERVER['SERVER_NAME'], 'localhost') === false;
    if ($google_analytics_id && $not_localhost) {
        $this->Js->buffer("_gaq.push(['_trackPageview', '" . $this->request->here . "']);");
    }
    echo $this->fetch('content');
    echo $this->Js->writeBuffer();
