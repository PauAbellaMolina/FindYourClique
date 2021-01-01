<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram;
use Telegram\Bot\Api;

class BotController extends Controller
{
    protected $telegram;
    protected $chat_id;
    protected $text;

    public function __construct() {
        $this->telegram = new Api("1480664779:AAH7c5hlHaAL4PBWhmvnP6_camD2rhE2HaA");
    }

    public function handleRequest(Request $request) {
        if(!isset($request['message']['text'])) {
            return;
        }

        $this->chat_id = $request['message']['chat']['id'];
        $this->text = $request['message']['text'];

        $instruction = explode(' ', $this->text);
        $cmd = $instruction[0];
        $arg = count($instruction) > 1 ? $instruction[1] : "";

        switch($cmd) {
            case'/test':
                $this->test($arg);
                break;
        }
    }

    public function test($type = null) {
        $message = "";

        $message .= $type;

        $this->sendMessage($message);
    }

    public function sendMessage($message, $parse_html = false) {
        $data = [
            'chat_id' => $this->chat_id,
            'text' => $message,
        ];

        if($parse_html) $data['parse_mode'] = 'HTML';

        $this->telegram->sendMessage($data);
    }

    public function setWebhook() {
        $response = Telegram::setWebhook(['url' => 'https://982b2a8e0c0c.ngrok.io/api/test']);
        dd($response);
    }

    public function updatedActivity() {
        $activity = Telegram::getUpdates();
        dd($activity);
    }
}
