<?php
namespace App;

class Slack
{
    public $content;
    public $curlResult;
    /**
     * Adds $line and a newline to the message being built
     *
     * @param string $line Line of text to add
     * @return void
     */
    public function addLine($line)
    {
        $this->content .= $line . "\n";
    }
    /**
     * Transforms special characters in the current message to make them Slack-friendly
     *
     * @return void
     */
    public function encodeContent()
    {
        $this->content = str_replace(
            ['&', '<', '>'],
            [
                urlencode('&amp;'),
                urlencode('&lt;'),
                urlencode('&gt;')
            ],
            $this->content
        );
    }
    /**
     * Sends a message to Slack
     *
     * @return bool
     */
    public function send()
    {
        $grahamDays = ['Sun', 'Tue', 'Thu', 'Sat'];
        $channel = !in_array(date('D'), $grahamDays) ? '@graham' : '@erica-dee-fox';
        $this->encodeContent();
        $data = 'payload=' . json_encode([
                'channel' => $channel,
                'text' => $this->content,
                'icon_emoji' => ':ticket:',
                'username' => 'Muncie Events Alerts'
            ]);
        $url = include dirname(dirname(__FILE__)) . '/config/slack_webhook_url.php';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->curlResult = curl_exec($ch);
        curl_close($ch);

        return $this->curlResult == 'ok';
    }
}
