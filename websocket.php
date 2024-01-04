<?php

$host = '127.0.0.1';
$port = 8080;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

if ($socket === false) {
    echo "Failed to create socket: " . socket_strerror(socket_last_error()) . "\n";
    die();
}

if (!socket_bind($socket, $host, $port)) {
    echo "Failed to bind socket: " . socket_strerror(socket_last_error($socket)) . "\n";
    die();
}

if (!socket_listen($socket)) {
    echo "Failed to listen on socket: " . socket_strerror(socket_last_error($socket)) . "\n";
    die();
}

echo "WebSocket server listening on $host:$port...\n";

while (true) {
    $clientSocket = socket_accept($socket);

    if ($clientSocket === false) {
        echo "Failed to accept incoming connection: " . socket_strerror(socket_last_error($socket)) . "\n";
        break;
    }

    // Handshake - Read the HTTP headers from the client
    $headers = socket_read($clientSocket, 1024);
    $key = '';

    if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $headers, $matches)) {
        $key = base64_encode(pack('H*', sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        // Send the WebSocket handshake response to the client
        $response = "HTTP/1.1 101 Switching Protocols\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            "Sec-WebSocket-Accept: $key\r\n\r\n";

        socket_write($clientSocket, $response);
    }



    // Now the connection is upgraded to WebSocket, and you can read/write WebSocket frames

    // Receive the WebSocket frame from the client
    $frame = socket_read($clientSocket, 1024);

    $decodedMessage = decodeWebSocketFrame($frame);

    echo "Received from client: $decodedMessage\n";

    // Echo the message back to the client
    $data = "Server: hello world";
    $encodedMessage = encodeWebSocketFrame(json_encode(['data' => $decodedMessage]));
    socket_write($clientSocket, $encodedMessage);

    // Close the WebSocket connection
    //socket_close($clientSocket);
}

socket_close($socket);

/**
 * Function to decode a WebSocket frame
 * @param string $frame
 * @return string
 */
function decodeWebSocketFrame($frame)
{
    $opcode = ord($frame[0]) & 0x0F;
    $payloadLength = ord($frame[1]) & 0x7F;

    if ($payloadLength === 126) {
        $payloadLength = unpack('n', substr($frame, 2, 2))[1];
        $payloadData = substr($frame, 4);
    } elseif ($payloadLength === 127) {
        $payloadLength = unpack('N', substr($frame, 2, 4))[1];
        $payloadData = substr($frame, 8);
    } else {
        $payloadData = substr($frame, 2);
    }

    if ($opcode === 1) {
        // Text frame
        return $payloadData;
    } else {
        // Handle other opcodes if needed
        return '';
    }
}

/**
 * Function to encode a message into a WebSocket frame
 * @param string $message
 * @return string
 */
function encodeWebSocketFrame($message)
{
    $encodedMessage = utf8_encode($message);
    $length = strlen($encodedMessage);
    $header = "\x81"; // 0x81 denotes a text frame (FIN + opcode)

    if ($length <= 125) {
        $header .= pack('C', $length);
    } elseif ($length <= 65535) {
        $header .= pack('C', 126) . pack('n', $length);
    } else {
        $header .= pack('C', 127) . pack('Q', $length);
    }

    return $header . $encodedMessage;
}
