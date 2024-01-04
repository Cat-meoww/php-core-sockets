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

// Bind the socket to the address and port
if (!socket_bind($socket, $host, $port)) {
    echo "Failed to bind socket: " . socket_strerror(socket_last_error($socket)) . "\n";
    die();
}

// Start listening for incoming connections
if (!socket_listen($socket)) {
    echo "Failed to listen on socket: " . socket_strerror(socket_last_error($socket)) . "\n";
    die();
}

echo "Server listening on $host:$port...\n";

while (true) {
    // Accept incoming connections
    $clientSocket = socket_accept($socket);
    if ($clientSocket === false) {
        echo "Failed to accept incoming connection: " . socket_strerror(socket_last_error($socket)) . "\n";
        break;
    }

    // Read data from the client
    $data = socket_read($clientSocket, 1024);
    echo "Received from client: $data\n";

    // Send a response back to the client
    $response = "Server: Hello, client! dude";
    socket_write($clientSocket, $response, strlen($response));

    // Close the client socket
    socket_close($clientSocket);
}

// Close the server socket
socket_close($socket);
