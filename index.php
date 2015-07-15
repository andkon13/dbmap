<?php
include_once 'dbmap/autoloader.php';
/** @var \dbmap\User $user */
$user = \dbmap\User::findById(1);
$partners = $user->partners;

$partner = $partners[2];
$u=$partner->user;
$c=1;