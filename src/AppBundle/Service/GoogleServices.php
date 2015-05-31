<?php

namespace AppBundle\Service;

class GoogleServices {

    /**
     * @var \Google_Client
     */
    private $_googleClient;

    /**
     * @var \Google_Service_Gmail
     */
    private $_gmailService;

    public function __construct(\Google_Client $googleClient, $clientSecretPath) {
        $this -> _googleClient = $googleClient;
        $this -> _googleClient->setAuthConfigFile($clientSecretPath);
        $this -> _googleClient->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/loginGoogle');
        $this -> _googleClient->addScope(\Google_Service_Gmail::GMAIL_READONLY);

        $this -> _gmailService = new \Google_Service_Gmail($this -> _googleClient);
    }

    public function createAuthUrl() {
        return $this -> _googleClient -> createAuthUrl();
    }

    public function authenticateCode($code) {
        $this -> _googleClient -> authenticate($code);
        return $this -> _googleClient -> getAccessToken();
    }

    public function refreshToken($accessToken) {
        $this -> _googleClient -> setAccessToken($accessToken);
        // Refresh the token if it's expired.
        if ($this -> _googleClient -> isAccessTokenExpired()) {
            $this -> _googleClient -> refreshToken($this -> _googleClient->getRefreshToken());
        }

        return $this -> _googleClient -> getAccessToken();
    }

    public function messages($count) {
        $user = 'me';
        $results = $this -> _gmailService -> users_messages -> listUsersMessages($user, array("maxResults" => 10))
            ->getMessages();

        $snippets = array();
        foreach($results as $value) {
            $message = $this -> _gmailService -> users_messages -> get($user, $value['id']);
            $snippets[] = array("snippet" => $message['snippet'], "sizeEstimate" => $message['sizeEstimate']);
        }

        return $snippets;
    }

}