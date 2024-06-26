<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Telegram;
use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;

class BotController extends Controller
{
    protected $telegram;
    protected $chat_id; //If in a group, this is the group chat id
    protected $user_id; //If in a group, this is the user chat id
    protected $username;
    protected $first_name;
    protected $last_name;
    protected $text;
    protected $spotifyToken;
    protected $userDB;
    protected $interest_name_1 = "1";
    protected $interest_name_2 = "2";
    protected $interest_name_3 = "3";
    protected $interest_name_4 = "4";
    protected $interest_name_5 = "5";

    public function __construct() {
        $this->telegram = new Api("1480664779:AAGJq3aY4PQmVhcGIIF_HK-NsxF1zvUPj1s");
    }

    public function handleRequest(Request $request) {
        if(!isset($request['message']['text'])) {
            return;
        }

        if($request['message']['chat']['type'] == "group") {
            $this->user_id = $request['message']['from']['id'];
        }

        $this->chat_id = $request['message']['chat']['id'];
        isset($request['message']['from']['first_name']) ?  $this->first_name = $request['message']['from']['first_name'] : $this->first_name = "";
        isset($request['message']['from']['last_name']) ? $this->last_name = $request['message']['from']['last_name'] : $this->last_name = "";
        $this->text = $request['message']['text'];

        //Username control check
        if(!isset($request['message']['from']['username'])) {
            $this->sendMessage("You need to have a <i><strong>username</strong></i> to use FindYourClique.\nCome back once you set one on your Telegram profile!", null, true);
            return;
        }

        $this->username = $request['message']['from']['username'];

        if(isset($this->user_id)) {
            if(User::where('chat_id', '=', $this->user_id)->get() == "[]") {
                $this->userDB = null;
            } else {
                try{
                    $this->userDB = User::where('chat_id', '=', $this->user_id)->get()[0];
                    $this->interest_name_1 = $this->userDB->interest_name_1;
                    $this->interest_name_2 = $this->userDB->interest_name_2;
                    $this->interest_name_3 = $this->userDB->interest_name_3;
                    $this->interest_name_4 = $this->userDB->interest_name_4;
                    $this->interest_name_5 = $this->userDB->interest_name_5;
                    $this->spotifyToken = $this->userDB->spotify_api_token;
                } catch(\Exception $e) {
                }
            }
        } else {
            if(User::where('chat_id', '=', $this->chat_id)->get() == "[]") {
                $this->userDB = null;
            } else {
                try{
                    $this->userDB = User::where('chat_id', '=', $this->chat_id)->get()[0];
                    $this->interest_name_1 = $this->userDB->interest_name_1;
                    $this->interest_name_2 = $this->userDB->interest_name_2;
                    $this->interest_name_3 = $this->userDB->interest_name_3;
                    $this->interest_name_4 = $this->userDB->interest_name_4;
                    $this->interest_name_5 = $this->userDB->interest_name_5;
                    $this->spotifyToken = $this->userDB->spotify_api_token;
                } catch(\Exception $e) {
                }
            }
        }

        $instruction = explode(' ',$this->text);

        if(preg_match('/Token: */', $this->text)) {
            $this->spotifyToken = count($instruction)>1 ? $instruction[1] : "";
            $this->text = $instruction[0];
        }

        switch($this->text) {
            case'/start@FindYourCliqueBot':
            case'/start':
                $this->start();
                break;
            case'/delete@FindYourCliqueBot':
            case'/delete':
                $this->delete();
                break;
            case'/help@FindYourCliqueBot':
            case'/help':
                $this->help();
                break;
            case'Next':
                $this->next();
                break;
            case'Token:':
                $this->setToken();
                break;
            case'/SetGroup@FindYourCliqueBot':
            case'/SetGroup':
            case'SetGroup':
                $this->setGroup();
                break;
            case'/Interests@FindYourCliqueBot':
            case'/interests@FindYourCliqueBot':
            case'/Interests':
            case'/interests':
            case'Interests':
            case'interests':
                $this->interests();
                break;
            case'/🡸GoBack@FindYourCliqueBot':
            case'/🡸GoBack':
            case'🡸GoBack':
                $this->goBack();
                break;
            case'/Match@FindYourCliqueBot':
            case'/match@FindYourCliqueBot':
            case'/Match':
            case'/match':
            case'Match':
            case'match':
                $this->match();
                break;
            case '/'.$this->interest_name_1.'@FindYourCliqueBot':
            case '/'.$this->interest_name_1:
            case $this->interest_name_1:
                $this->findMutualInterest1();
                break;
            case '/'.$this->interest_name_2.'@FindYourCliqueBot':
            case '/'.$this->interest_name_2:
            case $this->interest_name_2:
                $this->findMutualInterest2();
                break;
            case '/'.$this->interest_name_3.'@FindYourCliqueBot':
            case '/'.$this->interest_name_3:
            case $this->interest_name_3:
                $this->findMutualInterest3();
                break;
            case '/'.$this->interest_name_4.'@FindYourCliqueBot':
            case '/'.$this->interest_name_4:
            case $this->interest_name_4:
                $this->findMutualInterest4();
                break;
            case '/'.$this->interest_name_5.'@FindYourCliqueBot':
            case '/'.$this->interest_name_5:
            case $this->interest_name_5:
                $this->findMutualInterest5();
                break;
            default:
                break;
        }
    }

  //--------------------------------------------------------------------------//
 //-------------------------------METHODS-----------------------------------//
//------------------------------------------------------------------------//

    //Method for the "/start" input
    public function start() {
        $message = "";

        if(isset($this->user_id)) {
            $keyboard = [
                ['Next']
            ];
            $reply_markup = Keyboard::make([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);
            $message .= "I sent you a private message, please follow the instructions there.\n";
            $message .= "Once you are all set up, click <strong><i>Next</i></strong>\n";
            $this->sendMessage($message, $reply_markup, true);

            $keyboard = [
                ['Next']
            ];
            $reply_markup = Keyboard::make([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);
            $message = "";
            $message .= "Please, head out to this webpage and follow the instructions there.\n";
            $message .= "findyourclique.pauabella.dev\n";
            $message .= "Once you got your token, click <strong><i>Next</i></strong>\n";
            $this->sendMessage($message, $reply_markup, true, $this->user_id);
        } else {
            $message .= "Please, head out to this webpage and follow the instructions there.\n";
            $message .= "findyourclique.pauabella.dev\n";
            $message .= "Once you got your token, click <strong><i>Next</i></strong>\n";

            $keyboard = [
                ['Next']
            ];

            $reply_markup = Keyboard::make([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);

            $this->sendMessage($message, $reply_markup, true);
        }
    }

    //Method for the "/delete" input
    public function delete() {
        if(isset($this->user_id)) {
            $message = "";
            $message .= "You <strong>can't</strong> use this command im a group, please do it in a <strong>private conversation</strong> with me.\n";

            $this->sendMessage($message, null, true);
            return;
        }

        try {
            $user = User::where('chat_id', '=', $this->chat_id)->delete();

            $message = "";
            $message .= "<strong>Done, your user has been deleted!</strong>\n";

            $this->sendMessage($message, null, true);
        } catch(\Exception $e) {
            $message = "";
            $message .= "<strong>Something went wrong...</strong>\n";

            $this->sendMessage($message, null, true);
        }
    }

    //Method for the "Next" input
    public function next() {
        if(isset($this->user_id)) {
            if($user = User::where('chat_id', '=', $this->user_id)->get() == "[]") {
                $message = "";
                $message .= "<strong>Hmm... Seems like you haven't completed the steps in the private conversation with me yet.</strong>\n";

                $keyboard = [
                    ['Next']
                ];
                $reply_markup = Keyboard::make([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ]);

                $this->sendMessage($message, $reply_markup, true);

                $this->start();
                return;
            }
            $this->goBack();
            $user = User::where('chat_id', '=', $this->user_id)->get()[0];
            //If in a group and user has a different group_id than the group he is trying to match in, update the group_id of the user to the chat_id of the group
            if($user->group_id != $this->chat_id) {
                try{
                    $user->group_id = $this->chat_id;
                    $user->save();
                } catch(\Exception $e) {
                    $this->sendMessage($message, null, true);
                }
            }
            return;
        }

        $message = "";
        $message .= "<strong>Got your token? Great!\n";
        $message .= "Send me the token like this:</strong>\n";
        $message .= "Token: <i>YOUR TOKEN</i>\n";

        $this->sendMessage($message, null, true);
    }

    //Method for the "Token:" input
    public function setToken() {
        if(isset($this->user_id)) {
            return;
        }

        try {
            $user = User::where('chat_id', '=', $this->chat_id)->delete();
        } catch(\Exception $e) {
            $message = "";
            $message .= "<strong>Something went wrong...</strong>\n";

            $this->sendMessage($message, null, true);
        }

        try{
            //Create new user
            $user = new User;
            $user->chat_id = $this->chat_id;
            $user->username = $this->username;
            $user->first_name = $this->first_name;
            $user->last_name = $this->last_name;
            $user->spotify_api_token = $this->spotifyToken;
            $user->interests_set = 0;
            $user->created_at = date('Y-m-d H:m:s');
            $user->updated_at = date('Y-m-d H:m:s');
            $user->save();

            $message = "";
            $message .= "<strong>Alright, token set!</strong>\n";
            $message .= "This is your token:\n";
            $message .= "<code>".$this->spotifyToken."</code>\n";
            $message .= "Click on <i><strong>Interests</strong></i> to find out your interests and save them to your profile.\n";

            $keyboard = [
                ['Interests']
            ];

            $reply_markup = Keyboard::make([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);

            $this->sendMessage($message, $reply_markup, true);
        } catch(\Exception $e) {
            $message = "";
            $message .= "<strong>Something went wrong...</strong>\n";

            $this->sendMessage($message, null, true);
        }
    }

    //Method for the "SetGroup" input
    public function setGroup() {
        if(!isset($this->user_id)) {
            return;
        } else {
            if($user = User::where('chat_id', '=', $this->user_id)->get() == "[]") {
                $message = "";
                $message .= "<strong>Hmm... Seems like you haven't completed the steps in the private conversation with me yet.</strong>\n";

                $keyboard = [
                    ['Next']
                ];
                $reply_markup = Keyboard::make([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ]);

                $this->sendMessage($message, $reply_markup, true);

                $this->start();
                return;
            } else {
                $user = User::where('chat_id', '=', $this->user_id)->get()[0];

                $message = "";
                try{
                    $user->group_id = $this->chat_id;
                    $user->save();

                    $message .= "<strong>Nice! Now I'll match you with users in this group.</strong>\n";
                } catch(\Exception $e) {
                    $message .= "<strong>Something went wrong...</strong>\n";
                }
                $this->sendMessage($message, null, true);
            }
        }
    }

    //Method for the "🡸GoBack" input
    public function goBack() {
        $message = "";
        $message .= "<strong>Here you can access the bot features!</strong>\n";
        $message .= "\n";
        if(isset($this->user_id)) {
            $message .= "<strong>- <u>Interests</u></strong> will tell you your interests and popup some buttons for you to find users from this group with your same interests!\n";
            $message .= "\n";
            $message .= "<strong>- <u>Match</u></strong> will match you with users from this group based on your musical interests!\n";
        } else {
            $message .= "<strong>- <u>Interests</u></strong> will tell you your interests and popup some buttons for you to find users with your same interests!\n";
            $message .= "\n";
            $message .= "<strong>- <u>Match</u></strong> will match you with users based on your musical interests!\n";
        }

        $keyboard = [
            ["Interests", "Match"]
        ];

        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $this->sendMessage($message, $reply_markup, true);
    }

    //Method for the "Interests" input
    public function interests() {
        $request = Http::withToken($this->spotifyToken)
        ->get('https://api.spotify.com/v1/me/top/artists?time_range=short_term&limit=5');

        $response = json_decode($request);

        if(isset($this->user_id)) {
            if($user = User::where('chat_id', '=', $this->user_id)->get() == "[]") {
                $message = "";
                $message .= "<strong>Hmm... Seems like you haven't completed the steps in the private conversation with me yet.</strong>\n";

                $keyboard = [
                    ['Next']
                ];
                $reply_markup = Keyboard::make([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ]);

                $this->sendMessage($message, $reply_markup, true);

                $this->start();
                return;
            }
            $user = User::where('chat_id', '=', $this->user_id)->get()[0];
        } else {
            if($user = User::where('chat_id', '=', $this->chat_id)->get() == "[]") {
                return;
            }
            $user = User::where('chat_id', '=', $this->chat_id)->get()[0];
        }

        if(isset($response->items)) {
            $message = "";
            $message .= "These are your interests @".$this->username.":\n";
            $message .= "- ".$response->items[0]->name."\n";
            $message .= "- ".$response->items[1]->name."\n";
            $message .= "- ".$response->items[2]->name."\n";
            $message .= "- ".$response->items[3]->name."\n";
            $message .= "- ".$response->items[4]->name."\n";

            // $message .= "<strong>You can look for users matching your interests.</strong>\n";

            $keyboard = [
                [$this->interest_name_1, $this->interest_name_2],
                [$this->interest_name_3, $this->interest_name_4, $this->interest_name_5],
                ["🡸GoBack"]
            ];

            $reply_markup = Keyboard::make([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);

            if($user->interests_set == 0) {
                try{
                    $user->interests_set = 1;
                    $user->interest_code_1 = $response->items[0]->id;
                    $user->interest_name_1 = $response->items[0]->name;
                    $user->interest_code_2 = $response->items[1]->id;
                    $user->interest_name_2 = $response->items[1]->name;
                    $user->interest_code_3 = $response->items[2]->id;
                    $user->interest_name_3 = $response->items[2]->name;
                    $user->interest_code_4 = $response->items[3]->id;
                    $user->interest_name_4 = $response->items[3]->name;
                    $user->interest_code_5 = $response->items[4]->id;
                    $user->interest_name_5 = $response->items[4]->name;
                    $user->save();

                    $message .= "<strong>I've remembered your interests, nice!</strong>\n";
                    !isset($this->user_id) ? $message .= "If you came from a <strong>group</strong>, you can now go back!\n" : $message .= "";

                    try{
                        isset($this->user_id) ? $this->userDB = User::where('chat_id', '=', $this->user_id)->get()[0] : $this->userDB = User::where('chat_id', '=', $this->chat_id)->get()[0];
                        $this->interest_name_1 = $this->userDB->interest_name_1;
                        $this->interest_name_2 = $this->userDB->interest_name_2;
                        $this->interest_name_3 = $this->userDB->interest_name_3;
                        $this->interest_name_4 = $this->userDB->interest_name_4;
                        $this->interest_name_5 = $this->userDB->interest_name_5;
                        $this->spotifyToken = $this->userDB->spotify_api_token;
                    } catch(\Exception $e) {
                    }

                    $this->sendMessage($message, null, true);
                    //Since its the first time user gets here, dont show the interests buttons, show the menu instead (aka calling the goBack function)
                    $this->goBack();
                } catch(\Exception $e) {
                    $message .= "<strong>Something went wrong while I was trying to remember your interests...</strong>\n";
                    $this->sendMessage($message, null, true);
                }
            } else {
                $this->sendMessage($message, $reply_markup, true);
            }
        } else {
            if(isset($this->user_id)) {
                $message = "";
                $message .= "<strong>Something went wrong...</strong>\n";
                $message .= "<strong>Your token has probably expired</strong>\n";
                $this->sendMessage($message, null, true);
                $this->start();
            } else {
                $message = "";
                $message .= "<strong>Something went wrong...</strong>\n";
                $message .= "<strong>Your token has probably expired</strong>\n";
                $message .= "Please, head out to this webpage and follow the instructions there.\n";
                $message .= "findyourclique.pauabella.dev\n";
                $message .= "Once you got your token, click <strong><i>Next</i></strong>\n";

                $keyboard = [
                    ['Next']
                ];

                $reply_markup = Keyboard::make([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ]);

                $this->sendMessage($message, $reply_markup, true);
            }
        }
    }

    public function match() {
        //If in a group and user has a different group_id than the group he is trying to match in, update the group_id of the user to the chat_id of the group
        if(isset($this->user_id)) {
            if($user = User::where('chat_id', '=', $this->user_id)->get() == "[]") {
                $message = "";
                $message .= "<strong>Hmm... Seems like you haven't completed the steps in the private conversation with me yet.</strong>\n";

                $keyboard = [
                    ['Next']
                ];
                $reply_markup = Keyboard::make([
                    'keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ]);

                $this->sendMessage($message, $reply_markup, true);

                $this->start();
                return;
            }
            $user = User::where('chat_id', '=', $this->user_id)->get()[0];

            if($user->group_id != $this->chat_id) {
                try{
                    $user->group_id = $this->chat_id;
                    $user->save();
                } catch(\Exception $e) {
                    $this->sendMessage($message, null, true);
                }
            }
        }

        $message = "";

        $find100 = $this->find100();
        $find80 = $this->find80();
        $find60 = $this->find60();

        //If not matched with anyone, which is sad, send a message and return
        if($find100 == "[]" && $find80 == "[]" && $find60 == "[]") {
            $message .= "We couldn't find anyone with at least a 60% match with you.\n";
            $message .= "That's sad :(\n";
            $message .= "<strong>You can still try to find people with your same interests in the <i>Interests</i> section!</strong>\n";

            $keyboard = [
                ["🡸GoBack"]
            ];

            $reply_markup = Keyboard::make([
                'keyboard' => $keyboard,
                'resize_keyboard' => true,
                'one_time_keyboard' => true
            ]);

            $this->sendMessage($message, $reply_markup, true);
            return;
        }

        //100% message construction
        if($find100 != "[]") {
            $message .= "<strong>Wow look @".$this->username.", these users have your exact same interests!</strong>\n";
            foreach ($find100 as $mutual) {
                $message .= "- @".$mutual->username."\n";
            }
        }
        $message .= "\n";

        //80% message construction
        if($find80 != "[]") {
            foreach ($find80 as $key => $mutual) {
                if(str_contains($message ,$mutual->username)) {
                    unset($find80[$key]);
                }
            }
        }
        if($find100 != "[]" && $find80 != "[]") {
            $message .= "<strong>You also really match (80%) with these people!</strong>\n";
            foreach ($find80 as $mutual) {
                $message .= "- @".$mutual->username."\n";
            }
        } else if($find100 == "[]" && $find80 != "[]") {
            $message .= "<strong>@".$this->username." found some users you highly match with (80%)!</strong>\n";
            foreach ($find80 as $mutual) {
                $message .= "- @".$mutual->username."\n";
            }
        }
        $message .= "\n";

        //60% message construction
        if($find60 != "[]") {
            foreach ($find60 as $key => $mutual) {
                if(str_contains($message ,$mutual->username)) {
                    unset($find60[$key]);
                }
            }
        }
        if($find100 != "[]" && $find80 != "[]" && $find60 != "[]") {
            $message .= "<strong>Finally you also match with these right here (60%)</strong>\n";
            foreach ($find60 as $mutual) {
                $message .= "- @".$mutual->username."\n";
            }
        } else if($find100 == "[]" && $find80 != "[]" && $find60 != "[]") {
            $message .= "<strong>You also match (60%) with these people!</strong>\n";
            foreach ($find60 as $mutual) {
                $message .= "- @".$mutual->username."\n";
            }
        } else if($find100 == "[]" && $find80 == "[]" && $find60 != "[]") {
            $message .= "<strong>@".$this->username." found some users you certainly match with (60%)!</strong>\n";
            foreach ($find60 as $mutual) {
                $message .= "- @".$mutual->username."\n";
            }
        }

        $keyboard = [
            ["🡸GoBack"]
        ];

        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $this->sendMessage($message, $reply_markup, true);
    }

    //Method for the 100% match
    public function find100() {
        if($user = User::where('chat_id', '=', $this->user_id)->get() == "[]") {
            return;
        }
        if(isset($this->user_id)) {
            $user = User::where('chat_id', '=', $this->user_id)->get()[0];
            //If in a group and user has a different group_id than the group he is trying to match in, update the group_id of the user to the chat_id of the group
            if($user->group_id != $this->chat_id) {
                try{
                    $user->group_id = $this->chat_id;
                    $user->save();
                } catch(\Exception $e) {
                    $this->sendMessage($message, null, true);
                }
            }
            $scopedUsers = User::where('chat_id', '!=', $user->chat_id)->where('group_id', '=', $this->chat_id);
        } else {
            if($user = User::where('chat_id', '=', $this->chat_id)->get() == "[]") {
                return;
            }
            $user = User::where('chat_id', '=', $this->chat_id)->get()[0];
            $scopedUsers = User::where('chat_id', '!=', $user->chat_id);
        }
        $mutuals = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_1', '=', $user->interest_code_1)
                    ->orWhere('interest_code_1', '=', $user->interest_code_2)
                    ->orWhere('interest_code_1', '=', $user->interest_code_3)
                    ->orWhere('interest_code_1', '=', $user->interest_code_4)
                    ->orWhere('interest_code_1', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_2', '=', $user->interest_code_1)
                    ->orWhere('interest_code_2', '=', $user->interest_code_2)
                    ->orWhere('interest_code_2', '=', $user->interest_code_3)
                    ->orWhere('interest_code_2', '=', $user->interest_code_4)
                    ->orWhere('interest_code_2', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_3', '=', $user->interest_code_1)
                    ->orWhere('interest_code_3', '=', $user->interest_code_2)
                    ->orWhere('interest_code_3', '=', $user->interest_code_3)
                    ->orWhere('interest_code_3', '=', $user->interest_code_4)
                    ->orWhere('interest_code_3', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_4', '=', $user->interest_code_1)
                    ->orWhere('interest_code_4', '=', $user->interest_code_2)
                    ->orWhere('interest_code_4', '=', $user->interest_code_3)
                    ->orWhere('interest_code_4', '=', $user->interest_code_4)
                    ->orWhere('interest_code_4', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_5', '=', $user->interest_code_1)
                    ->orWhere('interest_code_5', '=', $user->interest_code_2)
                    ->orWhere('interest_code_5', '=', $user->interest_code_3)
                    ->orWhere('interest_code_5', '=', $user->interest_code_4)
                    ->orWhere('interest_code_5', '=', $user->interest_code_5);
            });
        })->get();

        return $mutuals;
    }

    //Method for the 80% match
    public function find80() {
        if(isset($this->user_id)) {
            if($user = User::where('chat_id', '=', $this->user_id)->get() == "[]") {
                return;
            }
            $user = User::where('chat_id', '=', $this->user_id)->get()[0];
            //If in a group and user has a different group_id than the group he is trying to match in, update the group_id of the user to the chat_id of the group
            if($user->group_id != $this->chat_id) {
                try{
                    $user->group_id = $this->chat_id;
                    $user->save();
                } catch(\Exception $e) {
                    $this->sendMessage($message, null, true);
                }
            }
            $scopedUsers = User::where('chat_id', '!=', $user->chat_id)->where('group_id', '=', $this->chat_id);
        } else {
            if($user = User::where('chat_id', '=', $this->chat_id)->get() == "[]") {
                return;
            }
            $user = User::where('chat_id', '=', $this->chat_id)->get()[0];
            $scopedUsers = User::where('chat_id', '!=', $user->chat_id);
        }
        $aux1 = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_1', '=', $user->interest_code_1)
                    ->orWhere('interest_code_1', '=', $user->interest_code_2)
                    ->orWhere('interest_code_1', '=', $user->interest_code_3)
                    ->orWhere('interest_code_1', '=', $user->interest_code_4)
                    ->orWhere('interest_code_1', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_2', '=', $user->interest_code_1)
                    ->orWhere('interest_code_2', '=', $user->interest_code_2)
                    ->orWhere('interest_code_2', '=', $user->interest_code_3)
                    ->orWhere('interest_code_2', '=', $user->interest_code_4)
                    ->orWhere('interest_code_2', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_3', '=', $user->interest_code_1)
                    ->orWhere('interest_code_3', '=', $user->interest_code_2)
                    ->orWhere('interest_code_3', '=', $user->interest_code_3)
                    ->orWhere('interest_code_3', '=', $user->interest_code_4)
                    ->orWhere('interest_code_3', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_4', '=', $user->interest_code_1)
                    ->orWhere('interest_code_4', '=', $user->interest_code_2)
                    ->orWhere('interest_code_4', '=', $user->interest_code_3)
                    ->orWhere('interest_code_4', '=', $user->interest_code_4)
                    ->orWhere('interest_code_4', '=', $user->interest_code_5);
            });
        })->get();

        $aux2 = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_1', '=', $user->interest_code_1)
                    ->orWhere('interest_code_1', '=', $user->interest_code_2)
                    ->orWhere('interest_code_1', '=', $user->interest_code_3)
                    ->orWhere('interest_code_1', '=', $user->interest_code_4)
                    ->orWhere('interest_code_1', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_2', '=', $user->interest_code_1)
                    ->orWhere('interest_code_2', '=', $user->interest_code_2)
                    ->orWhere('interest_code_2', '=', $user->interest_code_3)
                    ->orWhere('interest_code_2', '=', $user->interest_code_4)
                    ->orWhere('interest_code_2', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_3', '=', $user->interest_code_1)
                    ->orWhere('interest_code_3', '=', $user->interest_code_2)
                    ->orWhere('interest_code_3', '=', $user->interest_code_3)
                    ->orWhere('interest_code_3', '=', $user->interest_code_4)
                    ->orWhere('interest_code_3', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_5', '=', $user->interest_code_1)
                    ->orWhere('interest_code_5', '=', $user->interest_code_2)
                    ->orWhere('interest_code_5', '=', $user->interest_code_3)
                    ->orWhere('interest_code_5', '=', $user->interest_code_4)
                    ->orWhere('interest_code_5', '=', $user->interest_code_5);
            });
        })->get();

        $aux3 = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_1', '=', $user->interest_code_1)
                    ->orWhere('interest_code_1', '=', $user->interest_code_2)
                    ->orWhere('interest_code_1', '=', $user->interest_code_3)
                    ->orWhere('interest_code_1', '=', $user->interest_code_4)
                    ->orWhere('interest_code_1', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_2', '=', $user->interest_code_1)
                    ->orWhere('interest_code_2', '=', $user->interest_code_2)
                    ->orWhere('interest_code_2', '=', $user->interest_code_3)
                    ->orWhere('interest_code_2', '=', $user->interest_code_4)
                    ->orWhere('interest_code_2', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_4', '=', $user->interest_code_1)
                    ->orWhere('interest_code_4', '=', $user->interest_code_2)
                    ->orWhere('interest_code_4', '=', $user->interest_code_3)
                    ->orWhere('interest_code_4', '=', $user->interest_code_4)
                    ->orWhere('interest_code_4', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_5', '=', $user->interest_code_1)
                    ->orWhere('interest_code_5', '=', $user->interest_code_2)
                    ->orWhere('interest_code_5', '=', $user->interest_code_3)
                    ->orWhere('interest_code_5', '=', $user->interest_code_4)
                    ->orWhere('interest_code_5', '=', $user->interest_code_5);
            });
        })->get();

        $aux4 = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_1', '=', $user->interest_code_1)
                    ->orWhere('interest_code_1', '=', $user->interest_code_2)
                    ->orWhere('interest_code_1', '=', $user->interest_code_3)
                    ->orWhere('interest_code_1', '=', $user->interest_code_4)
                    ->orWhere('interest_code_1', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_3', '=', $user->interest_code_1)
                    ->orWhere('interest_code_3', '=', $user->interest_code_2)
                    ->orWhere('interest_code_3', '=', $user->interest_code_3)
                    ->orWhere('interest_code_3', '=', $user->interest_code_4)
                    ->orWhere('interest_code_3', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_4', '=', $user->interest_code_1)
                    ->orWhere('interest_code_4', '=', $user->interest_code_2)
                    ->orWhere('interest_code_4', '=', $user->interest_code_3)
                    ->orWhere('interest_code_4', '=', $user->interest_code_4)
                    ->orWhere('interest_code_4', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_5', '=', $user->interest_code_1)
                    ->orWhere('interest_code_5', '=', $user->interest_code_2)
                    ->orWhere('interest_code_5', '=', $user->interest_code_3)
                    ->orWhere('interest_code_5', '=', $user->interest_code_4)
                    ->orWhere('interest_code_5', '=', $user->interest_code_5);
            });
        })->get();

        $aux5 = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_2', '=', $user->interest_code_1)
                    ->orWhere('interest_code_2', '=', $user->interest_code_2)
                    ->orWhere('interest_code_2', '=', $user->interest_code_3)
                    ->orWhere('interest_code_2', '=', $user->interest_code_4)
                    ->orWhere('interest_code_2', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_3', '=', $user->interest_code_1)
                    ->orWhere('interest_code_3', '=', $user->interest_code_2)
                    ->orWhere('interest_code_3', '=', $user->interest_code_3)
                    ->orWhere('interest_code_3', '=', $user->interest_code_4)
                    ->orWhere('interest_code_3', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_4', '=', $user->interest_code_1)
                    ->orWhere('interest_code_4', '=', $user->interest_code_2)
                    ->orWhere('interest_code_4', '=', $user->interest_code_3)
                    ->orWhere('interest_code_4', '=', $user->interest_code_4)
                    ->orWhere('interest_code_4', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_5', '=', $user->interest_code_1)
                    ->orWhere('interest_code_5', '=', $user->interest_code_2)
                    ->orWhere('interest_code_5', '=', $user->interest_code_3)
                    ->orWhere('interest_code_5', '=', $user->interest_code_4)
                    ->orWhere('interest_code_5', '=', $user->interest_code_5);
            });
        })->get();

        $mutuals = new \Illuminate\Database\Eloquent\Collection;
        $mutuals = $mutuals->merge($aux1);
        $mutuals = $mutuals->merge($aux2);
        $mutuals = $mutuals->merge($aux3);
        $mutuals = $mutuals->merge($aux4);
        $mutuals = $mutuals->merge($aux5);

        return $mutuals;
    }

    //Method for the 60% match
    public function find60() {
        if(isset($this->user_id)) {
            if($user = User::where('chat_id', '=', $this->user_id)->get() == "[]") {
                return;
            }
            $user = User::where('chat_id', '=', $this->user_id)->get()[0];
            //If in a group and user has a different group_id than the group he is trying to match in, update the group_id of the user to the chat_id of the group
            if($user->group_id != $this->chat_id) {
                try{
                    $user->group_id = $this->chat_id;
                    $user->save();
                } catch(\Exception $e) {
                    $this->sendMessage($message, null, true);
                }
            }
            $scopedUsers = User::where('chat_id', '!=', $user->chat_id)->where('group_id', '=', $this->chat_id);
        } else {
            if($user = User::where('chat_id', '=', $this->chat_id)->get() == "[]") {
                return;
            }
            $user = User::where('chat_id', '=', $this->chat_id)->get()[0];
            $scopedUsers = User::where('chat_id', '!=', $user->chat_id);
        }
        $aux1 = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_1', '=', $user->interest_code_1)
                    ->orWhere('interest_code_1', '=', $user->interest_code_2)
                    ->orWhere('interest_code_1', '=', $user->interest_code_3)
                    ->orWhere('interest_code_1', '=', $user->interest_code_4)
                    ->orWhere('interest_code_1', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_2', '=', $user->interest_code_1)
                    ->orWhere('interest_code_2', '=', $user->interest_code_2)
                    ->orWhere('interest_code_2', '=', $user->interest_code_3)
                    ->orWhere('interest_code_2', '=', $user->interest_code_4)
                    ->orWhere('interest_code_2', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_3', '=', $user->interest_code_1)
                    ->orWhere('interest_code_3', '=', $user->interest_code_2)
                    ->orWhere('interest_code_3', '=', $user->interest_code_3)
                    ->orWhere('interest_code_3', '=', $user->interest_code_4)
                    ->orWhere('interest_code_3', '=', $user->interest_code_5);
            });
        })->get();

        $aux2 = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_1', '=', $user->interest_code_1)
                    ->orWhere('interest_code_1', '=', $user->interest_code_2)
                    ->orWhere('interest_code_1', '=', $user->interest_code_3)
                    ->orWhere('interest_code_1', '=', $user->interest_code_4)
                    ->orWhere('interest_code_1', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_2', '=', $user->interest_code_1)
                    ->orWhere('interest_code_2', '=', $user->interest_code_2)
                    ->orWhere('interest_code_2', '=', $user->interest_code_3)
                    ->orWhere('interest_code_2', '=', $user->interest_code_4)
                    ->orWhere('interest_code_2', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_4', '=', $user->interest_code_1)
                    ->orWhere('interest_code_4', '=', $user->interest_code_2)
                    ->orWhere('interest_code_4', '=', $user->interest_code_3)
                    ->orWhere('interest_code_4', '=', $user->interest_code_4)
                    ->orWhere('interest_code_4', '=', $user->interest_code_5);
            });
        })->get();

        $aux3 = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_1', '=', $user->interest_code_1)
                    ->orWhere('interest_code_1', '=', $user->interest_code_2)
                    ->orWhere('interest_code_1', '=', $user->interest_code_3)
                    ->orWhere('interest_code_1', '=', $user->interest_code_4)
                    ->orWhere('interest_code_1', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_2', '=', $user->interest_code_1)
                    ->orWhere('interest_code_2', '=', $user->interest_code_2)
                    ->orWhere('interest_code_2', '=', $user->interest_code_3)
                    ->orWhere('interest_code_2', '=', $user->interest_code_4)
                    ->orWhere('interest_code_2', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_5', '=', $user->interest_code_1)
                    ->orWhere('interest_code_5', '=', $user->interest_code_2)
                    ->orWhere('interest_code_5', '=', $user->interest_code_3)
                    ->orWhere('interest_code_5', '=', $user->interest_code_4)
                    ->orWhere('interest_code_5', '=', $user->interest_code_5);
            });
        })->get();

        $aux4 = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_1', '=', $user->interest_code_1)
                    ->orWhere('interest_code_1', '=', $user->interest_code_2)
                    ->orWhere('interest_code_1', '=', $user->interest_code_3)
                    ->orWhere('interest_code_1', '=', $user->interest_code_4)
                    ->orWhere('interest_code_1', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_3', '=', $user->interest_code_1)
                    ->orWhere('interest_code_3', '=', $user->interest_code_2)
                    ->orWhere('interest_code_3', '=', $user->interest_code_3)
                    ->orWhere('interest_code_3', '=', $user->interest_code_4)
                    ->orWhere('interest_code_3', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_4', '=', $user->interest_code_1)
                    ->orWhere('interest_code_4', '=', $user->interest_code_2)
                    ->orWhere('interest_code_4', '=', $user->interest_code_3)
                    ->orWhere('interest_code_4', '=', $user->interest_code_4)
                    ->orWhere('interest_code_4', '=', $user->interest_code_5);
            });
        })->get();

        $aux5 = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_1', '=', $user->interest_code_1)
                    ->orWhere('interest_code_1', '=', $user->interest_code_2)
                    ->orWhere('interest_code_1', '=', $user->interest_code_3)
                    ->orWhere('interest_code_1', '=', $user->interest_code_4)
                    ->orWhere('interest_code_1', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_3', '=', $user->interest_code_1)
                    ->orWhere('interest_code_3', '=', $user->interest_code_2)
                    ->orWhere('interest_code_3', '=', $user->interest_code_3)
                    ->orWhere('interest_code_3', '=', $user->interest_code_4)
                    ->orWhere('interest_code_3', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_5', '=', $user->interest_code_1)
                    ->orWhere('interest_code_5', '=', $user->interest_code_2)
                    ->orWhere('interest_code_5', '=', $user->interest_code_3)
                    ->orWhere('interest_code_5', '=', $user->interest_code_4)
                    ->orWhere('interest_code_5', '=', $user->interest_code_5);
            });
        })->get();

        $aux6 = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_1', '=', $user->interest_code_1)
                    ->orWhere('interest_code_1', '=', $user->interest_code_2)
                    ->orWhere('interest_code_1', '=', $user->interest_code_3)
                    ->orWhere('interest_code_1', '=', $user->interest_code_4)
                    ->orWhere('interest_code_1', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_4', '=', $user->interest_code_1)
                    ->orWhere('interest_code_4', '=', $user->interest_code_2)
                    ->orWhere('interest_code_4', '=', $user->interest_code_3)
                    ->orWhere('interest_code_4', '=', $user->interest_code_4)
                    ->orWhere('interest_code_4', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_5', '=', $user->interest_code_1)
                    ->orWhere('interest_code_5', '=', $user->interest_code_2)
                    ->orWhere('interest_code_5', '=', $user->interest_code_3)
                    ->orWhere('interest_code_5', '=', $user->interest_code_4)
                    ->orWhere('interest_code_5', '=', $user->interest_code_5);
            });
        })->get();

        $aux7 = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_2', '=', $user->interest_code_1)
                    ->orWhere('interest_code_2', '=', $user->interest_code_2)
                    ->orWhere('interest_code_2', '=', $user->interest_code_3)
                    ->orWhere('interest_code_2', '=', $user->interest_code_4)
                    ->orWhere('interest_code_2', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_3', '=', $user->interest_code_1)
                    ->orWhere('interest_code_3', '=', $user->interest_code_2)
                    ->orWhere('interest_code_3', '=', $user->interest_code_3)
                    ->orWhere('interest_code_3', '=', $user->interest_code_4)
                    ->orWhere('interest_code_3', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_4', '=', $user->interest_code_1)
                    ->orWhere('interest_code_4', '=', $user->interest_code_2)
                    ->orWhere('interest_code_4', '=', $user->interest_code_3)
                    ->orWhere('interest_code_4', '=', $user->interest_code_4)
                    ->orWhere('interest_code_4', '=', $user->interest_code_5);
            });
        })->get();

        $aux8 = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_2', '=', $user->interest_code_1)
                    ->orWhere('interest_code_2', '=', $user->interest_code_2)
                    ->orWhere('interest_code_2', '=', $user->interest_code_3)
                    ->orWhere('interest_code_2', '=', $user->interest_code_4)
                    ->orWhere('interest_code_2', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_3', '=', $user->interest_code_1)
                    ->orWhere('interest_code_3', '=', $user->interest_code_2)
                    ->orWhere('interest_code_3', '=', $user->interest_code_3)
                    ->orWhere('interest_code_3', '=', $user->interest_code_4)
                    ->orWhere('interest_code_3', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_5', '=', $user->interest_code_1)
                    ->orWhere('interest_code_5', '=', $user->interest_code_2)
                    ->orWhere('interest_code_5', '=', $user->interest_code_3)
                    ->orWhere('interest_code_5', '=', $user->interest_code_4)
                    ->orWhere('interest_code_5', '=', $user->interest_code_5);
            });
        })->get();

        $aux9 = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_2', '=', $user->interest_code_1)
                    ->orWhere('interest_code_2', '=', $user->interest_code_2)
                    ->orWhere('interest_code_2', '=', $user->interest_code_3)
                    ->orWhere('interest_code_2', '=', $user->interest_code_4)
                    ->orWhere('interest_code_2', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_4', '=', $user->interest_code_1)
                    ->orWhere('interest_code_4', '=', $user->interest_code_2)
                    ->orWhere('interest_code_4', '=', $user->interest_code_3)
                    ->orWhere('interest_code_4', '=', $user->interest_code_4)
                    ->orWhere('interest_code_4', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_5', '=', $user->interest_code_1)
                    ->orWhere('interest_code_5', '=', $user->interest_code_2)
                    ->orWhere('interest_code_5', '=', $user->interest_code_3)
                    ->orWhere('interest_code_5', '=', $user->interest_code_4)
                    ->orWhere('interest_code_5', '=', $user->interest_code_5);
            });
        })->get();

        $aux10 = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_3', '=', $user->interest_code_1)
                    ->orWhere('interest_code_3', '=', $user->interest_code_2)
                    ->orWhere('interest_code_3', '=', $user->interest_code_3)
                    ->orWhere('interest_code_3', '=', $user->interest_code_4)
                    ->orWhere('interest_code_3', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_4', '=', $user->interest_code_1)
                    ->orWhere('interest_code_4', '=', $user->interest_code_2)
                    ->orWhere('interest_code_4', '=', $user->interest_code_3)
                    ->orWhere('interest_code_4', '=', $user->interest_code_4)
                    ->orWhere('interest_code_4', '=', $user->interest_code_5);
            })
            ->where(function($query) use ($user) {
                $query->orWhere('interest_code_5', '=', $user->interest_code_1)
                    ->orWhere('interest_code_5', '=', $user->interest_code_2)
                    ->orWhere('interest_code_5', '=', $user->interest_code_3)
                    ->orWhere('interest_code_5', '=', $user->interest_code_4)
                    ->orWhere('interest_code_5', '=', $user->interest_code_5);
            });
        })->get();

        $mutuals = new \Illuminate\Database\Eloquent\Collection;
        $mutuals = $mutuals->merge($aux1);
        $mutuals = $mutuals->merge($aux2);
        $mutuals = $mutuals->merge($aux3);
        $mutuals = $mutuals->merge($aux4);
        $mutuals = $mutuals->merge($aux5);
        $mutuals = $mutuals->merge($aux6);
        $mutuals = $mutuals->merge($aux7);
        $mutuals = $mutuals->merge($aux8);
        $mutuals = $mutuals->merge($aux9);
        $mutuals = $mutuals->merge($aux10);

        return $mutuals;
    }

    //Method for the "FindMutual" input
    public function findMutualInterest1() {
        if(isset($this->user_id)) {
            if($user = User::where('chat_id', '=', $this->user_id)->get() == "[]") {
                return;
            }
            $user = User::where('chat_id', '=', $this->user_id)->get()[0];
            //If in a group and user has a different group_id than the group he is trying to match in, update the group_id of the user to the chat_id of the group
            if($user->group_id != $this->chat_id) {
                try{
                    $user->group_id = $this->chat_id;
                    $user->save();
                } catch(\Exception $e) {
                    $this->sendMessage($message, null, true);
                }
            }
            $scopedUsers = User::where('chat_id', '!=', $user->chat_id)->where('group_id', '=', $this->chat_id);
        } else {
            if($user = User::where('chat_id', '=', $this->chat_id)->get() == "[]") {
                return;
            }
            $user = User::where('chat_id', '=', $this->chat_id)->get()[0];
            $scopedUsers = User::where('chat_id', '!=', $user->chat_id);
        }
        $mutuals = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_1', '=', $user->interest_code_1)
                    ->orWhere('interest_code_2', '=', $user->interest_code_1)
                    ->orWhere('interest_code_3', '=', $user->interest_code_1)
                    ->orWhere('interest_code_4', '=', $user->interest_code_1)
                    ->orWhere('interest_code_5', '=', $user->interest_code_1);
            });
        })->get();

        $message = "";

        if($mutuals == "[]") {
            $message .= "We couldn't find anyone who also likes";
        } else {
            $message .= "<strong>Found them!</strong> Users who also like <strong>".$user->interest_name_1."</strong>\n";
            foreach ($mutuals as $mutual) {
                $message .= "- @".$mutual->username."\n";
            }
        }

        $keyboard = [
            [$this->interest_name_1, $this->interest_name_2],
            [$this->interest_name_3, $this->interest_name_4, $this->interest_name_5],
            ["🡸GoBack"]
        ];

        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $this->sendMessage($message, $reply_markup, true);
    }
    public function findMutualInterest2() {
        if(isset($this->user_id)) {
            if($user = User::where('chat_id', '=', $this->user_id)->get() == "[]") {
                return;
            }
            $user = User::where('chat_id', '=', $this->user_id)->get()[0];
            //If in a group and user has a different group_id than the group he is trying to match in, update the group_id of the user to the chat_id of the group
            if($user->group_id != $this->chat_id) {
                try{
                    $user->group_id = $this->chat_id;
                    $user->save();
                } catch(\Exception $e) {
                    $this->sendMessage($message, null, true);
                }
            }
            $scopedUsers = User::where('chat_id', '!=', $user->chat_id)->where('group_id', '=', $this->chat_id);
        } else {
            if($user = User::where('chat_id', '=', $this->chat_id)->get() == "[]") {
                return;
            }
            $user = User::where('chat_id', '=', $this->chat_id)->get()[0];
            $scopedUsers = User::where('chat_id', '!=', $user->chat_id);
        }
        $mutuals = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_1', '=', $user->interest_code_2)
                    ->orWhere('interest_code_2', '=', $user->interest_code_2)
                    ->orWhere('interest_code_3', '=', $user->interest_code_2)
                    ->orWhere('interest_code_4', '=', $user->interest_code_2)
                    ->orWhere('interest_code_5', '=', $user->interest_code_2);
            });
        })->get();

        $message = "";

        if($mutuals == "[]") {
            $message .= "We couldn't find anyone who also likes";
        } else {
            $message .= "<strong>Found them!</strong> Users who also like <strong>".$user->interest_name_2."</strong>\n";
            foreach ($mutuals as $mutual) {
                $message .= "- @".$mutual->username."\n";
            }
        }

        $keyboard = [
            [$this->interest_name_1, $this->interest_name_2],
            [$this->interest_name_3, $this->interest_name_4, $this->interest_name_5],
            ["🡸GoBack"]
        ];

        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $this->sendMessage($message, $reply_markup, true);
    }
    public function findMutualInterest3() {
        if(isset($this->user_id)) {
            if($user = User::where('chat_id', '=', $this->user_id)->get() == "[]") {
                return;
            }
            $user = User::where('chat_id', '=', $this->user_id)->get()[0];
            //If in a group and user has a different group_id than the group he is trying to match in, update the group_id of the user to the chat_id of the group
            if($user->group_id != $this->chat_id) {
                try{
                    $user->group_id = $this->chat_id;
                    $user->save();
                } catch(\Exception $e) {
                    $this->sendMessage($message, null, true);
                }
            }
            $scopedUsers = User::where('chat_id', '!=', $user->chat_id)->where('group_id', '=', $this->chat_id);
        } else {
            if($user = User::where('chat_id', '=', $this->chat_id)->get() == "[]") {
                return;
            }
            $user = User::where('chat_id', '=', $this->chat_id)->get()[0];
            $scopedUsers = User::where('chat_id', '!=', $user->chat_id);
        }
        $mutuals = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_1', '=', $user->interest_code_3)
                    ->orWhere('interest_code_2', '=', $user->interest_code_3)
                    ->orWhere('interest_code_3', '=', $user->interest_code_3)
                    ->orWhere('interest_code_4', '=', $user->interest_code_3)
                    ->orWhere('interest_code_5', '=', $user->interest_code_3);
            });
        })->get();

        $message = "";

        if($mutuals == "[]") {
            $message .= "We couldn't find anyone who also likes";
        } else {
            $message .= "<strong>Found them!</strong> Users who also like <strong>".$user->interest_name_3."</strong>\n";
            foreach ($mutuals as $mutual) {
                $message .= "- @".$mutual->username."\n";
            }
        }

        $keyboard = [
            [$this->interest_name_1, $this->interest_name_2],
            [$this->interest_name_3, $this->interest_name_4, $this->interest_name_5],
            ["🡸GoBack"]
        ];

        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $this->sendMessage($message, $reply_markup, true);
    }
    public function findMutualInterest4() {
        if(isset($this->user_id)) {
            if($user = User::where('chat_id', '=', $this->user_id)->get() == "[]") {
                return;
            }
            $user = User::where('chat_id', '=', $this->user_id)->get()[0];
            //If in a group and user has a different group_id than the group he is trying to match in, update the group_id of the user to the chat_id of the group
            if($user->group_id != $this->chat_id) {
                try{
                    $user->group_id = $this->chat_id;
                    $user->save();
                } catch(\Exception $e) {
                    $this->sendMessage($message, null, true);
                }
            }
            $scopedUsers = User::where('chat_id', '!=', $user->chat_id)->where('group_id', '=', $this->chat_id);
        } else {
            if($user = User::where('chat_id', '=', $this->chat_id)->get() == "[]") {
                return;
            }
            $user = User::where('chat_id', '=', $this->chat_id)->get()[0];
            $scopedUsers = User::where('chat_id', '!=', $user->chat_id);
        }
        $mutuals = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_1', '=', $user->interest_code_4)
                    ->orWhere('interest_code_2', '=', $user->interest_code_4)
                    ->orWhere('interest_code_3', '=', $user->interest_code_4)
                    ->orWhere('interest_code_4', '=', $user->interest_code_4)
                    ->orWhere('interest_code_5', '=', $user->interest_code_4);
            });
        })->get();

        $message = "";

        if($mutuals == "[]") {
            $message .= "We couldn't find anyone who also likes";
        } else {
            $message .= "<strong>Found them!</strong> Users who also like <strong>".$user->interest_name_4."</strong>\n";
            foreach ($mutuals as $mutual) {
                $message .= "- @".$mutual->username."\n";
            }
        }

        $keyboard = [
            [$this->interest_name_1, $this->interest_name_2],
            [$this->interest_name_3, $this->interest_name_4, $this->interest_name_5],
            ["🡸GoBack"]
        ];

        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $this->sendMessage($message, $reply_markup, true);
    }
    public function findMutualInterest5() {
        if(isset($this->user_id)) {
            if($user = User::where('chat_id', '=', $this->user_id)->get() == "[]") {
                return;
            }
            $user = User::where('chat_id', '=', $this->user_id)->get()[0];
            //If in a group and user has a different group_id than the group he is trying to match in, update the group_id of the user to the chat_id of the group
            if($user->group_id != $this->chat_id) {
                try{
                    $user->group_id = $this->chat_id;
                    $user->save();
                } catch(\Exception $e) {
                    $this->sendMessage($message, null, true);
                }
            }
            $scopedUsers = User::where('chat_id', '!=', $user->chat_id)->where('group_id', '=', $this->chat_id);
        } else {
            if($user = User::where('chat_id', '=', $this->chat_id)->get() == "[]") {
                return;
            }
            $user = User::where('chat_id', '=', $this->chat_id)->get()[0];
            $scopedUsers = User::where('chat_id', '!=', $user->chat_id);
        }
        $mutuals = $scopedUsers
        ->where(function($query) use ($user) {
            $query->where(function($query) use ($user) {
                $query->orWhere('interest_code_1', '=', $user->interest_code_5)
                    ->orWhere('interest_code_2', '=', $user->interest_code_5)
                    ->orWhere('interest_code_3', '=', $user->interest_code_5)
                    ->orWhere('interest_code_4', '=', $user->interest_code_5)
                    ->orWhere('interest_code_5', '=', $user->interest_code_5);
            });
        })->get();

        $message = "";

        if($mutuals == "[]") {
            $message .= "We couldn't find anyone who also likes";
        } else {
            $message .= "<strong>Found them!</strong> Users who also like <strong>".$user->interest_name_5."</strong>\n";
            foreach ($mutuals as $mutual) {
                $message .= "- @".$mutual->username."\n";
            }
        }

        $keyboard = [
            [$this->interest_name_1, $this->interest_name_2],
            [$this->interest_name_3, $this->interest_name_4, $this->interest_name_5],
            ["🡸GoBack"]
        ];

        $reply_markup = Keyboard::make([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        $this->sendMessage($message, $reply_markup, true);
    }

    //Method for the "/help" input
    public function help() {
        $message = "";
        $message .= "<strong>About <u>FindYourClique</u></strong>\n";
        $message .= "This Telegram Bot lets you find users with same musical interests as you.\n";
        $message .= "The goal is to create communities based on musical interests and ultimetly making you find your clique!\n";
        $message .= "\n";

        if(isset($this->user_id)) {
            $message .= "<strong>How to use it</strong>\n";
            $message .= "- Type /start.\n";
            $message .= "- The bot will send you a private message\n";
            $message .= "- Follow the instructions given\n";
            $message .= "- Come back when you finished the setup process\n";
            $message .= "\n";
            $message .= "<strong>Groups</strong>\n";
            $message .= "Yep, FindYourClique works inside groups!\n";
            $message .= "Once you have followed the 5 simple steps, the bot will match you between the people you are in the group with.\n";
            $message .= "Discover who you have musical interests in common with in any group!\n";
            $message .= "\n";
            $message .= "<strong>Commands</strong>\n";
            $message .= "/start - This is the first command that will start it all!\n";
            $message .= "/Interests - This command will tell you your interests and you will be able to find users from this group with your same interests!\n";
            $message .= "/Match - This command will match you with users from the same group based on your musical interests!\n";
            $message .= "/help - Confused? Use this to get some help!\n";
            $message .= "/delete - Wanna go? :( Use this to delete your user from out records.\n";
        } else {
            $message .= "<strong>How to use it in 5 simple steps</strong>\n";
            $message .= "- Type /start.\n";
            $message .= "- The bot will now tell you to go to the FindYourClique Website.\n";
            $message .= "- Go there, login with your Spotify account and come back with the token given copied.\n";
            $message .= "- Send the token to the bot the way it will tell you to.\n";
            $message .= "- That's it! Now you can play with the Interests and Match features yourself.\n";
            $message .= "\n";
            $message .= "<strong>What about groups?</strong>\n";
            $message .= "Yep, FindYourClique also works inside groups!\n";
            $message .= "Once you have followed the 5 simple steps, the bot will behave in groups the same way it does here but matching you between the people you are in the group with.\n";
            $message .= "Discover who you have musical interests in common with in any group!\n";
        }

        if(isset($this->user_id)) {
            $message .= "\n";
            $message .= "<strong>Wanna go?</strong>\n";
            $message .= "If you want to delete your user from our records, go to a private conversation with me and use the command ➜ /delete\n";
        } else {
            $message .= "\n";
            $message .= "<strong>Wanna go?</strong>\n";
            $message .= "If you want to delete your user from our records, use the command ➜ /delete\n";
        }

        $this->sendMessage($message, null, true);
    }

  //--------------------------------------------------------------------------//
 //-------------------------------Static Stuff------------------------------//
//------------------------------------------------------------------------//

    //Universal send message function
    public function sendMessage($message, $reply_markup = null, $parse_html = false, $chat_id = null) {

        $data = [
            'chat_id' => $chat_id,
            'text' => $message,
        ];

        if($chat_id == null) $data['chat_id'] = $this->chat_id;

        if($parse_html) $data['parse_mode'] = 'HTML';

        if($reply_markup != null) $data['reply_markup'] = $reply_markup;

        // Telegram::sendMessage($data);
        $this->telegram->sendMessage($data);
    }

    public function setWebhook() {
        $response = Telegram::setWebhook(['url' => 'https://pauabella.dev/FindYourClique/api/webhook']);
        dd($response);
    }

    public function removeWebhook() {
        $response = Telegram::removeWebhook();
        dd($response);
    }

    public function updatedActivity() {
        $activity = Telegram::getUpdates();
        dd($activity);
    }
}
