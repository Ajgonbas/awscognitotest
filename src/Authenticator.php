<?php

namespace ApexApi;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\Exception\AwsException;

class Authenticator
{
    private $clientId;
    private $userPoolId;
    private $awsClient;

    public function __construct($clientId, $userPoolId, $awsClient)
    {
        $this->clientId = $clientId;
        $this->userPoolId = $userPoolId;
        $this->awsClient = $awsClient;
    }

    public function getAuthorizationHeader()
    {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            return null;
        }

        return str_replace('Bearer ', '', $headers['Authorization']);
    }

    public function authenticate()
    {
        $token = $this->getAuthorizationHeader();

        if (!$token) {
            return false;
        }

        try {
            // Validate the token with AWS Cognito
            $result = $this->awsClient->getUser([
                'AccessToken' => $token,
            ]);

            return $result;
        } catch (AwsException $e) {
            return false;
        }
    }

    public function initiateAuth($username, $password)
    {
        try {
            $result = $this->awsClient->initiateAuth([
                'AuthFlow' => 'USER_PASSWORD_AUTH',
                'ClientId' => $this->clientId,
                'AuthParameters' => [
                    'USERNAME' => $username,
                    'PASSWORD' => $password,
                ],
            ]);

            return $result['AuthenticationResult']['IdToken'];
        } catch (AwsException $e) {
            return false;
        }
    }
}