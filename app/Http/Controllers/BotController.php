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
    protected $chat_id;
    protected $username;
    protected $first_name;
    protected $last_name;
    protected $text;
    protected $spotifyToken;

    public function __construct() {
        $this->telegram = new Api("1480664779:AAGJq3aY4PQmVhcGIIF_HK-NsxF1zvUPj1s");
    }

    public function handleRequest(Request $request) {
        if(!isset($request['message']['text'])) {
            return;
        }

        $this->chat_id = $request['message']['chat']['id'];
        isset($request['message']['chat']['first_name']) ?  $this->first_name = $request['message']['chat']['first_name'] : $this->first_name = "";
        isset($request['message']['chat']['last_name']) ? $this->last_name = $request['message']['chat']['last_name'] : $this->last_name = "";
        $this->text = $request['message']['text'];

        //Username control check
        if(!isset($request['message']['chat']['username'])) {
            $this->sendMessage("You need to have a <i><strong>username</strong></i> to use FindYourClique.\nCome back once you set one on your Telegram profile!", null, true);
            return;
        }

        $this->username = $request['message']['chat']['username'];

        if($user = User::where('chat_id', '=', $this->chat_id)->get() == "[]") {
        } else {
            try{
                $user = User::where('chat_id', '=', $this->chat_id)->get();
                $this->spotifyToken = $user[0]['spotify_api_token'];
            } catch(\Exception $e) {
            }
        }

        $instruction = explode(' ',$this->text);
        $cmd = $instruction[0];
        // $arg = count($instruction)>1?$instruction[1]:"";

        switch($cmd) {
            case'/start':
                $this->start();
                break;
            case'/delete':
                $this->delete();
                break;
            case'Next':
                $this->next();
                break;
            case'Token:':
                $this->spotifyToken = count($instruction)>1 ? $instruction[1] : "";
                $this->setToken();
                break;
            case'Interests':
                $this->interests();
                break;
            case'100':
                $this->find100();
                break;
            case'80':
                $this->find80();
                break;
            case'1':
                $this->findMutualInterest1();
                break;
            case'2':
                $this->findMutualInterest2();
                break;
            case'3':
                $this->findMutualInterest3();
                break;
            case'4':
                $this->findMutualInterest4();
                break;
            case'5':
                $this->findMutualInterest5();
                break;
            case'/tkn':
                $this->tkn();
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

    //Method for the "/delete" input
    public function delete() {
        try {
            $user = User::where('chat_id', '=', $this->chat_id)->delete();

            $message = "";
            $message .= "<strong>Done, your user has been deleted!</strong>\n";

            $this->sendMessage($message, null, true);
        } catch(\Exception $e) {
            $message = "";
            $message .= "<strong>Something went wrong...1</strong>\n";

            $this->sendMessage($message, null, true);
        }
    }

    //Method for the "Next" input
    public function next() {
        $message = "";
        $message .= "<strong>Got your token? Great!\n";
        $message .= "Send me the token like this:</strong>\n";
        $message .= "Token: <i>YOUR TOKEN</i>\n";

        $this->sendMessage($message, null, true);
    }

    //Method for the "Token:" input
    public function setToken() {

        if($user = User::where('chat_id', '=', $this->chat_id)->get() == "[]") {
        } else {
            try {
                $user = User::where('chat_id', '=', $this->chat_id)->delete();
            } catch(\Exception $e) {
                $message = "";
                $message .= "<strong>Something went wrong...1</strong>\n";

                $this->sendMessage($message, null, true);
            }
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
            $message .= "<strong>Something went wrong...2</strong>\n";

            $this->sendMessage($message, null, true);
        }
    }

    //Method for the "Interests" input
    public function interests() {
        $request = Http::withToken($this->spotifyToken)
        ->get('https://api.spotify.com/v1/me/top/artists?time_range=short_term&limit=5');

        $response = json_decode($request);

        if(isset($response->items)) {
            $message = "";
            $message .= "These are your interests:\n";
            $message .= "- ".$response->items[0]->name."\n";
            $message .= "- ".$response->items[1]->name."\n";
            $message .= "- ".$response->items[2]->name."\n";
            $message .= "- ".$response->items[3]->name."\n";
            $message .= "- ".$response->items[4]->name."\n";

            $user = User::where('chat_id', '=', $this->chat_id)->get()[0];
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
                    $this->sendMessage($message, null, true);
                } catch(\Exception $e) {
                    $message .= "<strong>Something went wrong while I was trying to remember your interests...</strong>\n";
                    $this->sendMessage($message, null, true);
                }
            } else {
                $this->sendMessage($message, null, true);
            }
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

    //Method for the "100" input
    public function find100() {
        if($user = User::where('chat_id', '=', $this->chat_id)->get() == "[]") {
            return;
        }
        $user = User::where('chat_id', '=', $this->chat_id)->get()[0];
        $mutuals = User::where('chat_id', '!=', $user->chat_id)
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

        $message = "";

        if($mutuals == "[]") {
            $message .= "We couldn't find anyone who also likes";
        } else {
            $message .= "<strong>Found them!</strong>\n";
            foreach ($mutuals as $mutual) {
                $message .= "- @".$mutual->username."\n";
            }
        }
        $this->sendMessage($message, null, true);
    }

    //Method for the "80" input
    public function find80() {
        if($user = User::where('chat_id', '=', $this->chat_id)->get() == "[]") {
            return;
        }
        $user = User::where('chat_id', '=', $this->chat_id)->get()[0];
        $aux1 = User::where('chat_id', '!=', $user->chat_id)
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

        $aux2 = User::where('chat_id', '!=', $user->chat_id)
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

        $aux3 = User::where('chat_id', '!=', $user->chat_id)
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

        $aux4 = User::where('chat_id', '!=', $user->chat_id)
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

        $aux5 = User::where('chat_id', '!=', $user->chat_id)
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

        $message = "";

        if($mutuals == "[]") {
            $message .= "We couldn't find anyone who also likes";
        } else {
            $message .= "<strong>Found them!</strong>\n";
            foreach ($mutuals as $mutual) {
                $message .= "- @".$mutual->username."\n";
            }
        }
        $this->sendMessage($message, null, true);
    }

    //Method for the "FindMutual" input
    public function findMutualInterest1() {
        if($user = User::where('chat_id', '=', $this->chat_id)->get() == "[]") {
            return;
        }
        $user = User::where('chat_id', '=', $this->chat_id)->get()[0];
        $mutuals = User::where('chat_id', '!=', $user->chat_id)->where('interest_code_1', '=', $user->interest_code_1)->get();

        $message = "";

        if($mutuals == "[]") {
            $message .= "We couldn't find anyone who also likes";
        } else {
            $message .= "<strong>Found them!</strong> Users who also like <strong>".$user->interest_name_1."</strong>\n";
            foreach ($mutuals as $mutual) {
                $message .= "- @".$mutual->username."\n";
            }
        }
        $this->sendMessage($message, null, true);
    }
    public function findMutualInterest2() {
        $user = User::where('chat_id', '=', $this->chat_id)->get()[0];
        $mutuals = User::where('chat_id', '!=', $user->chat_id)->where('interest_code_2', '=', $user->interest_code_2)->get();

        $message = "";

        if($mutuals == "[]") {
            $message .= "We couldn't find anyone who also likes";
        } else {
            $message .= "<strong>Found them!</strong> Users who also like <strong>".$user->interest_name_2."</strong>\n";
            foreach ($mutuals as $mutual) {
                $message .= "- @".$mutual->username."\n";
            }
        }
        $this->sendMessage($message, null, true);
    }
    public function findMutualInterest3() {
        $user = User::where('chat_id', '=', $this->chat_id)->get()[0];
        $mutuals = User::where('chat_id', '!=', $user->chat_id)->where('interest_code_3', '=', $user->interest_code_3)->get();

        $message = "";

        if($mutuals == "[]") {
            $message .= "We couldn't find anyone who also likes";
        } else {
            $message .= "<strong>Found them!</strong> Users who also like <strong>".$user->interest_name_3."</strong>\n";
            foreach ($mutuals as $mutual) {
                $message .= "- @".$mutual->username."\n";
            }
        }
        $this->sendMessage($message, null, true);
    }
    public function findMutualInterest4() {
        $user = User::where('chat_id', '=', $this->chat_id)->get()[0];
        $mutuals = User::where('chat_id', '!=', $user->chat_id)->where('interest_code_4', '=', $user->interest_code_4)->get();

        $message = "";

        if($mutuals == "[]") {
            $message .= "We couldn't find anyone who also likes";
        } else {
            $message .= "<strong>Found them!</strong> Users who also like <strong>".$user->interest_name_4."</strong>\n";
            foreach ($mutuals as $mutual) {
                $message .= "- @".$mutual->username."\n";
            }
        }
        $this->sendMessage($message, null, true);
    }
    public function findMutualInterest5() {
        $user = User::where('chat_id', '=', $this->chat_id)->get()[0];
        $mutuals = User::where('chat_id', '!=', $user->chat_id)->where('interest_code_5', '=', $user->interest_code_5)->get();

        $message = "";

        if($mutuals == "[]") {
            $message .= "We couldn't find anyone who also likes";
        } else {
            $message .= "<strong>Found them!</strong> Users who also like <strong>".$user->interest_name_5."</strong>\n";
            foreach ($mutuals as $mutual) {
                $message .= "- @".$mutual->username."\n";
            }
        }
        $this->sendMessage($message, null, true);
    }

  //--------------------------------------------------------------------------//
 //-------------------------------Static Stuff------------------------------//
//------------------------------------------------------------------------//


    //Method for the "Interests" input
    public function interestsDebug() {
        $user = User::where('chat_id', '=', '450828960')->get()[0];
        $aux1 = User::where('chat_id', '!=', '450828960')
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

        $aux2 = User::where('chat_id', '!=', '450828960')
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

        $aux3 = User::where('chat_id', '!=', '450828960')
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

        $aux4 = User::where('chat_id', '!=', '450828960')
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

        $aux5 = User::where('chat_id', '!=', '450828960')
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

        dd($mutuals);
    }


    //Debug method for the "/tkn" input. This just return the spotifyToken raw
    public function tkn() {
        $message = "";
        $message .= "This is your token:\n";
        $message .= "<code>".$this->spotifyToken."</code>";

        $this->sendMessage($message, null, true);
    }

    //Universal send message function
    public function sendMessage($message, $reply_markup = null, $parse_html = false) {
        $data = [
            'chat_id' => $this->chat_id,
            'text' => $message,
        ];

        if($parse_html) $data['parse_mode'] = 'HTML';

        if($reply_markup != null) $data['reply_markup'] = $reply_markup;

        // Telegram::sendMessage($data);
        $this->telegram->sendMessage($data);
    }

    public function setWebhook() {
        $response = Telegram::setWebhook(['url' => 'https://d3695a4256ab.ngrok.io/api/webhook']);
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
