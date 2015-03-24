<?php
require('../vendor/autoload.php');

// Logger setup
$log_handler = new Monolog\Handler\StreamHandler("php://stdout", Monolog\Logger::WARNING);
$log_handler->setFormatter(new Monolog\Formatter\LineFormatter("%message%"));
$logger = new Monolog\Logger("log");
$logger->pushHandler($log_handler);

// Fetch the expected config environment variables
$lifx_access_token = getenv('LIFX_ACCESS_TOKEN');
$bulb_selector     = getenv('BULB_SELECTOR');
$project_name      = getenv('PROJECT_NAME');
$branch_name       = getenv('BRANCH_NAME');
$webhook_token     = getenv('WEBHOOK_TOKEN');
%>

<% if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Process the webhook
  $request_event = $_SERVER['HTTP_X_BUILDKITE_EVENT'];
  $request_token = $_SERVER['HTTP_X_BUILDKITE_TOKEN'];
  $request_body = http_get_request_body();

  $logger->addInfo("Webhook event: {$request_event}");
  $logger->addInfo("Webhook token: {$request_token}");
  $logger->addInfo("Webhook request: {$request_body}");

  // TODO: Post to LIFX

} else {
?>
  <div style="font:24px Avenir,Helvetica;max-width:32em;margin:2em;line-height:1.3">
    <h1 style="font-size:1.5em">Huzzah! You’re almost there.</h1>
    <p style="color:#666">Now create a webhook in your <a href="https://buildkite.com/" style="color:black">Buildkite</a> notification settings with this URL, and the webhook token from the Heroku app’s config&nbsp;variables:</p>
    <p><? echo $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["REQUEST_HOST"]."/" ?></p>
  </div>
<?
}
