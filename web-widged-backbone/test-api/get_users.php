<?php
/***
 * NOTE: This shloud be used until JSONP support is implemented for cross domain communication
 */
$response = file_get_contents("http://78.130.204.197/who/api.php?format=json");
echo $response;