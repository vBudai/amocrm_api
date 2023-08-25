<?php

require "vendor/autoload.php";
require "auth_token.php";

$amoApi = new amo\AmoApi('vbudai297', $token);
$leads = $amoApi->getLeads();

echo '<pre>';
echo $leads;
echo '</pre>';