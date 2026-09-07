<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
ob_implicit_flush(true);
while (ob_get_level()) ob_end_flush();

require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');
$mode = $input['mode'] ?? 'general';

if (empty($message)) {
    echo "data: " . json_encode(['error' => 'Pesan tidak boleh kosong.']) . "\n\n";
    ob_flush();
    flush();
    exit;
}

$systemInstruction = ($mode === 'work') 
    ? "Anda adalah asisten kerja profesional. Jawab secara padat, jelas, dan efektif.\n\n"
    : "Anda adalah chatbot umum yang ramah dan solutif.\n\n";

$fullPrompt = $systemInstruction . "Pengguna: " . $message;

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:streamGenerateContent?alt=sse&key=" . GEMINI_API_KEY;

$data = [
    "contents" => [
        [
            "parts" => [
                ["text" => $fullPrompt]
            ]
        ]
    ]
];

$fullAiResponse = ""; 

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); 
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $chunk) use (&$fullAiResponse) {
   
    $lines = explode("\n", $chunk);
    foreach ($lines as $line) {
        if (strpos($line, 'data: ') === 0) {
            $jsonStr = substr($line, 6);
            $json = json_decode($jsonStr, true);
           
            $textChunk = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
            if ($textChunk !== '') {
                $fullAiResponse .= $textChunk;
                
                echo "data: " . json_encode(['chunk' => $textChunk]) . "\n\n";
                if (ob_get_level() > 0) ob_flush();
                flush();
            }
        }
    }
    return strlen($chunk);
});

curl_exec($ch);
curl_close($ch);

if (!empty($fullAiResponse)) {
    $stmt = $conn->prepare("INSERT INTO chat_history (mode, user_message, ai_response) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $mode, $message, $fullAiResponse);
    $stmt->execute();
}

echo "data: [DONE]\n\n";
if (ob_get_level() > 0) ob_flush();
flush();
?>