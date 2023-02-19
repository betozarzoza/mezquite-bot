<?php
use App\Http\Controllers\BotManController;
use App\Conversations\ExampleConversation;

$botman = resolve('botman');

$botman->hears('Hola', function ($bot) {
    $bot->reply('Hola! Soy el bot de Condominios El Mezquite, mucho gusto de ser tu vecino.');
});


$botman->hears('.*cuanto debo.*', function($bot) {
    $bot->startConversation(new ExampleConversation);
});

$botman->hears('Hello', function($bot) {
    $bot->startConversation(new ExampleConversation);
});

$botman->hears('call me {name}', function ($bot, $name) {
    $bot->reply('Your name is: '.$name);
});

$botman->hears('single response', function ($bot) {
    $bot->reply("Tell me more!");
});
