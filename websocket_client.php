<?php

$host = '127.0.0.1';
$port = 8080;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

if ($socket === false) {
    echo "Failed to create socket: " . socket_strerror(socket_last_error()) . "\n";
    die();
}

if (!socket_connect($socket, $host, $port)) {
    echo "Failed to connect to server: " . socket_strerror(socket_last_error($socket)) . "\n";
    die();
}

// Perform the WebSocket handshake with the server
$request = "GET / HTTP/1.1\r\n" .
    "Host: $host:$port\r\n" .
    "Upgrade: websocket\r\n" .
    "Connection: Upgrade\r\n" .
    "Sec-WebSocket-Key: " . base64_encode(openssl_random_pseudo_bytes(16)) . "\r\n" .
    "Sec-WebSocket-Version: 13\r\n\r\n";

socket_write($socket, $request);

// Read the WebSocket handshake response from the server
$response = socket_read($socket, 1024);
echo "WebSocket Handshake Response:\n$response\n";

// Implement WebSocket logic here

// Send a message to the server
$message = "Hello, WebSocket Server!";
$encodedMessage = encodeWebSocketFrame($message);
socket_write($socket, $encodedMessage);

// Close the WebSocket connection
socket_close($socket);


function encodeWebSocketFrame($message) {
    $length = strlen($message);
    $header = "\x81"; // 0x81 denotes a text frame (FIN + opcode)

    if ($length <= 125) {
        $header .= pack('C', $length);
    } elseif ($length <= 65535) {
        $header .= pack('C', 126) . pack('n', $length);
    } else {
        $header .= pack('C', 127) . pack('Q', $length);
    }

    return $header . $message;
}