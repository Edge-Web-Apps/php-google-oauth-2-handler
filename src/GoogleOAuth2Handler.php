<?php

namespace RapidWeb\GoogleOAuth2Handler;

use GuzzleHttp\Psr7\Request;

class GoogleOAuth2Handler
{
    private $clientId;
    private $clientSecret;
    private $scopes;
    private $refreshToken;
    private $client;
    private $redirectUrl;
    private $accessType;
    private $approvalPrompt;

    public $authUrl;

    public function __construct($clientId, $clientSecret, $scopes, $refreshToken = '', $redirectUrl = 'urn:ietf:wg:oauth:2.0:oob', $accessType = 'offline', $approvalPrompt = 'force' )
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->scopes = $scopes;
        $this->refreshToken = $refreshToken;
        $this->redirectUrl = $redirectUrl;
        $this->accessType = $accessType;
        $this->approvalPrompt = $approvalPrompt;

        $this->setupClient();
    }

    private function setupClient()
    {
        $this->client = new \Google_Client();

        $this->client->setClientId($this->clientId);
        $this->client->setClientSecret($this->clientSecret);
        $this->client->setRedirectUri($this->redirectUrl);
        $this->client->setAccessType($this->accessType);
        $this->client->setApprovalPrompt($this->approvalPrompt);

        foreach($this->scopes as $scope)  {
            $this->client->addScope($scope);
        }

        if ($this->refreshToken) {
            $this->client->refreshToken($this->refreshToken);
        } else {
            $this->authUrl = $this->client->createAuthUrl();
        }
    }

    public function getRefreshToken($authCode)
    {
        $this->client->authenticate($authCode);
        $accessToken = $this->client->getAccessToken();
        return $accessToken['refresh_token'];
    }

    public function performRequest($method, $url, $body = null)
    {
        $httpClient = $this->client->authorize();
        $request = new Request($method, $url, [], $body);
        $response = $httpClient->send($request);
        return $response;
    }

}
