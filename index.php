<?php
include_once 'dbmap/autoloader.php';
/** @var \dbmap\Client $client */
$client      = \dbmap\Client::findById(1);
$client->user_id = 'dsfsd';
$a = $client->validate();
$a = $client->getLastError();
$a           = 1;
