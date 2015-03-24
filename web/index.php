<?php
require('../vendor/autoload.php');

// Logger setup
$log_handler = new Monolog\Handler\StreamHandler("php://stdout", Monolog\Logger::WARNING);
$log_handler->setFormatter(new Monolog\Formatter\LineFormatter("%message%"));
$logger = new Monolog\Logger("log");
$logger->pushHandler($log_hander);

// Fetch the expected config environment variables
$lifx_access_token = getenv('LIFX_ACCESS_TOKEN');
$bulb_selector     = getenv('BULB_SELECTOR');
$project_name      = getenv('PROJECT_NAME');
$branch_name       = getenv('BRANCH_NAME');
$webhook_token     = getenv('WEBHOOK_TOKEN');

// Process the webhook
$request_event = $_SERVER['HTTP_X_BUILDKITE_EVENT'];
$request_token = $_SERVER['HTTP_X_BUILDKITE_TOKEN'];
$request_body = http_get_request_body();

$logger->addInfo("Webhook event: {$request_event}");
$logger->addInfo("Webhook token: {$request_token}");
$logger->addInfo("Webhook request: {$request_body}");
?>
