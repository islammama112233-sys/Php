<?php

require_once "config.php";

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if(isset($update["message"])) {

    $chat_id = $update["message"]["chat"]["id"];
    $text = $update["message"]["text"];

    if($text == "/start") {

        $reply = "Bot Online ✅";

        file_get_contents(API_URL . "sendMessage?chat_id=$chat_id&text=" . urlencode($reply));
    }
}

?>
