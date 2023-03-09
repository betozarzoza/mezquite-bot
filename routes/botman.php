<?php
use App\Http\Controllers\BotManController;
use App\Conversations\ExampleConversation;
use App\Conversations\AddMovementConversation;
use App\Conversations\GeneralOptionsConversation;
use App\Conversations\TestConversation;

$botman = resolve('botman');

$botman->hears('.*opciones.*', function ($bot) {
    $bot->startConversation(new GeneralOptionsConversation);
});

$botman->hears('hola', function ($bot) {
    $bot->startConversation(new GeneralOptionsConversation);
});

$botman->hears('.*cuanto debo.*', function($bot) {
    $bot->startConversation(new ExampleConversation);
});

$botman->hears('admin', function($bot) {
    $bot->startConversation(new AddMovementConversation);
});

$botman->hears('test', function($bot) {
    $bot->startConversation(new TestConversation);
});


