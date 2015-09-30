<?php
if (file_exists("../vendor/autoload.php")) {
  require("../vendor/autoload.php");
}

// Fetch the expected config environment variables
$lifx_access_token = getenv("LIFX_ACCESS_TOKEN");
$bulb_selector     = getenv("BULB_SELECTOR");
$webhook_token     = getenv("WEBHOOK_TOKEN");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Process the webhook
  $request_event = $_SERVER["HTTP_X_BUILDKITE_EVENT"];
  $request_webhook_token = $_SERVER["HTTP_X_BUILDKITE_TOKEN"];
  $request_body = file_get_contents("php://input");
  $request_json = json_decode($request_body, true);

  // Check the token for security
  if ($webhook_token != $request_webhook_token) {
    http_response_code(401);
    fwrite(STDOUT, "{$request_webhook_token} doesn't match expected webhook token {$webhook_token}");
    throw new Exception("Webhook token is invalid");
  }

  if ($request_event == "build.running") {
    fwrite(STDOUT, "Build running");
    post_to_lifx("/v1/lights/{$bulb_selector}/effects/breathe.json", [
      power_on   => false,
      color      => "yellow brightness:5%",
      from_color => "yellow brightness:35%",
      period     => 5,
      cycles     => 9999,
      persist    => true
    ]);
  }

  if ($request_event == "build.finished") {
    if ($request_json["build"]["state"] == "passed") {
      fwrite(STDOUT, "Build passed");
      post_to_lifx("/v1/lights/{$bulb_selector}/effects/breathe.json", [
        power_on   => false,
        color      => "green brightness:75%",
        from_color => "green brightness:10%",
        period     => 0.45,
        cycles     => 3,
        persist    => true,
        peak       => 0.2
      ]);
    } else {
      fwrite(STDOUT, "Build failed");
      post_to_lifx("/v1/lights/{$bulb_selector}/effects/breathe.json", [
        power_on   => false,
        color      => "red brightness:60%",
        from_color => "red brightness:25%",
        period     => 0.1,
        cycles     => 20,
        persist    => true,
        peak       => 0.2
      ]);
    }
  }

} else {
?>
  <div style="font:24px Avenir,Helvetica;max-width:32em;margin:2em;line-height:1.3">
    <h1 style="font-size:1.5em">Huzzah! You’re almost there.</h1>
    <p style="color:#666">Now create a webhook in your <a href="https://buildkite.com/" style="color:black">Buildkite</a> notification settings with this URL, and the webhook token from the Heroku app’s config&nbsp;variables.</p>
    <p>https://<?= $_SERVER["SERVER_NAME"] ?>/</p>
  </div>
<?
}

function post_to_lifx($path, $params) {
  global $lifx_access_token;
  $json_data = json_encode($params);
  return file_get_contents("https://api.lifx.com".$path, false, stream_context_create([
    "http" => [
      "method"  => "POST",
      "header"  => "Content-type: application/json\r\n".
                   "Connection: close\r\n".
                   "Content-length: ".strlen($json_data)."\r\n".
                   "Authorization: Bearer ".$lifx_access_token."\r\n",
      "content" => $json_data
    ]
  ]));
}
