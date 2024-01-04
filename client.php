<?php

// Define server settings
$host = '127.0.0.1';
$port = 8080;

// Create a socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    echo "Failed to create socket: " . socket_strerror(socket_last_error()) . "\n";
    die();
}

// Connect to the server
if (!socket_connect($socket, $host, $port)) {
    echo "Failed to connect to server: " . socket_strerror(socket_last_error($socket)) . "\n";
    die();
}

// Send data to the server
$message = "Client: Hello, server bro!";
socket_write($socket, $message, strlen($message));

// Read the response from the server
$response = socket_read($socket, 1024);
echo "Received from server: $response\n";


$message = "Client: Bye, server bro!";
socket_write($socket, $message, strlen($message));

// Close the socket
socket_close($socket);
