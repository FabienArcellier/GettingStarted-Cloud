<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/loginGoogle", name="loginGoogle")
     */
    public function loginGoogleAction(Request $request) {
        if($this -> isAuthentified($request)) {
            return $this->forward( 'AppBundle\\Controller\\DefaultController::indexAction');
        }

        $session = $request->getSession();
        $client = new \Google_Client();
        $secret_path = $this->get('kernel')->getRootDir() . '/Resources/client_secret.json';
        $client->setAuthConfigFile($secret_path);
        $client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/loginGoogle');
        $client->addScope(\Google_Service_Gmail::GMAIL_READONLY);

        $token = $request -> get('code', null);
        if ($token == null) {
            $auth_url = $client->createAuthUrl();
            return $this -> redirect($auth_url);
        } else {
            $client->authenticate($token);
            $session -> set('access_token', $client->getAccessToken());
            $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/';
            return $this -> redirect($redirect_uri);
        }
    }

    /**
     * @Route("/login", name="login")
     * @Template()
     */
    public function loginAction(Request $request)
    {
        if($this -> isAuthentified($request)) {
            return $this->forward( 'AppBundle\\Controller\\DefaultController::indexAction');
        }

        return array();
    }

    /**
     * @Route("/", name="index")
     * @Template()
     */
    public function indexAction(Request $request) {
        if($this -> isAuthentified($request) == false) {
            return $this->forward( 'AppBundle\\Controller\\DefaultController::loginAction');
        }

        return array();
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction(Request $request) {
        $session = $request->getSession();
        $session -> remove('access_token');
        return $this -> redirect('/');
    }

    public function isAuthentified(Request $request) {
        $session = $request->getSession();
        return $access_token = $session -> get('access_token', null) != null;
    }
}
