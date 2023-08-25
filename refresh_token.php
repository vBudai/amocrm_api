<?php

require_once "amo/AccessToken.php";

use amo\AccessToken;

$tokenClass = new AccessToken();
$tokenClass->refreshToken();
$token = getToken();