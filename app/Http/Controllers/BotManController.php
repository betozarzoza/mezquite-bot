<?php

namespace App\Http\Controllers;

use BotMan\BotMan\BotMan;
use Illuminate\Http\Request;
use App\Conversations\ExampleConversation;
use App\Balance;

class BotManController extends Controller
{
    /**
     * Place your BotMan logic here.
     */

    protected $house_number;

    public function handle()
    {
        $botman = app('botman');

        $botman->listen();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tinker()
    {
        return view('tinker');
    }

    /**
     * Loaded through routes/botman.php
     * @param  BotMan $bot
     */
    public function startConversation(BotMan $bot)
    {
        $bot->startConversation(new ExampleConversation());
    }

    public function howMuchDoIOwe()
    {
        $this->ask('Hola vecino, ¿De que casa es?, por favor escriba solo el numero', function(Answer $answer) {
            // Save result
            $this->house_number = $answer->getText();
            $balance = Balance::select('balance')->where('house', $this->house_number)->first();
            //print_r($balance->balance);

            $this->say('Vecino de la casa '.$balance->balance. 'usted esta al corriente');
        });
    }
}
