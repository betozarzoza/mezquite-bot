<?php
use App\Http\Controllers\BotManController;
use App\Conversations\ExampleConversation;
use App\Conversations\AddMovementConversation;

$botman = resolve('botman');

$botman->hears('Hola', function ($bot) {
    $bot->reply('Hola! Soy el bot de Condominios El Mezquite, mucho gusto de ser tu vecino.');
});


$botman->hears('.*cuanto debo.*', function($bot) {
    $bot->startConversation(new ExampleConversation);
});

$botman->hears('admin', function($bot) {
    $bot->startConversation(new AddMovementConversation);
});

