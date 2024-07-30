<?php

session_start();

require 'vendor/autoload.php';

use ApexApi\Router;
use ApexApi\Authenticator;
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;

// Replace these placeholders with your actual Cognito details
$clientId = 'YOUR_COGNITO_CLIENT_ID';
$userPoolId = 'YOUR_COGNITO_USER_POOL_ID';
$awsClient = new CognitoIdentityProviderClient([
    'version' => 'latest',
    'region'  => 'us-west-2', // Replace with your region
    'credentials' => [
        'key'    => 'YOUR_AWS_ACCESS_KEY_ID',
        'secret' => 'YOUR_AWS_SECRET_ACCESS_KEY',
    ],
]);

$authenticator = new Authenticator($clientId, $userPoolId, $awsClient);

// Authenticate the user and store the details in the session
//$userDetails = $authenticator->authenticate();
$userDetails = '12345';

if (!$userDetails) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$_SESSION['user'] = $userDetails;

$request = [
    'resource' => $_GET['resource'] ?? null,
    'action' => $_GET['action'] ?? null,
    'description' => $_POST['description'] ?? null,
    'user-assign' => $_POST['user-assign'] ?? null,
    'id' => $_POST['id'] ?? null
];

$router = new Router();
$router->route($request);