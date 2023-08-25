<?php

use amo\AccessToken;

$tokenClass = new AccessToken();
$tokenClass->createToken();
$token = $tokenClass->getToken();