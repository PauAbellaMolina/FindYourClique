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
    protected $spotifyToken;

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
        $arg = count($instruction)>1 ? $instruction[1] : "";
        $token = count($instruction)>2 ? $instruction[2] : "";

        switch($cmd) {
            case'/setup':
                $this->setup($arg, $token);
                break;
        }
    }

    public function setup($type = null, $token = null) {

        switch($type) {
            case 'setToken':
                $spotifyToken = $token;
                $message = "";
                $message .= "Alright, token set!\n";
                break;
            default:
                $message = "";
                $message .= "SETUP:\n";
                $message .= "Please, head out to this webpage and follow the instructions there.\n";
                $message .= "http://localhost:8080\n";
                $message .= "Alright, now send the token like this:\n";
                $message .= "/setup setToken YOUR_TOKEN";
                break;
        }

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
        $response = Telegram::setWebhook(['url' => 'https://490c47533ff2.ngrok.io/api/webhook']);
        dd($response);
    }

    public function updatedActivity() {
        $activity = Telegram::getUpdates();
        dd($activity);
    }
}
