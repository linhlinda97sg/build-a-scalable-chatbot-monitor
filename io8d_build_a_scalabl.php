<?php

class ChatbotMonitor {
    private $chatbots;
    private $logger;

    public function __construct($logger) {
        $this->logger = $logger;
        $this->chatbots = [];
    }

    public function addChatbot($chatbot) {
        $this->chatbots[] = $chatbot;
    }

    public function monitor() {
        foreach ($this->chatbots as $chatbot) {
            $response = $chatbot->getResponse();
            $this->logger->log($response);
            if ($response->getError()) {
                $this->logger->error("Error in chatbot " . $chatbot->getName() . ": " . $response->getMessage());
            }
        }
    }
}

class Chatbot {
    private $name;
    private $apiUrl;

    public function __construct($name, $apiUrl) {
        $this->name = $name;
        $this->apiUrl = $apiUrl;
    }

    public function getName() {
        return $this->name;
    }

    public function getResponse() {
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return new Response($response);
    }
}

class Response {
    private $response;
    private $error;

    public function __construct($response) {
        $this->response = $response;
        $this->error = json_decode($response, true)['error'] ?? false;
    }

    public function getError() {
        return $this->error;
    }

    public function getMessage() {
        return json_decode($this->response, true)['message'] ?? '';
    }
}

class Logger {
    public function log($message) {
        echo "Info: $message\n";
    }

    public function error($message) {
        echo "Error: $message\n";
    }
}

$logger = new Logger();

$chatbot1 = new Chatbot('Chatbot 1', 'https://api.chatbot1.com/response');
$chatbot2 = new Chatbot('Chatbot 2', 'https://api.chatbot2.com/response');

$monitor = new ChatbotMonitor($logger);
$monitor->addChatbot($chatbot1);
$monitor->addChatbot($chatbot2);

$monitor->monitor();

?>