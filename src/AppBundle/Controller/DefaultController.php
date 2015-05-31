<?php

namespace AppBundle\Controller;

use AppBundle\Service\GoogleServices;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{

    public function __construct() {
    }

    /**
     * @Route("/loginGoogle", name="loginGoogle")
     */
    public function loginGoogleAction(Request $request) {
        if($this -> isAuthentified($request)) {
            return $this->forward( 'AppBundle\\Controller\\DefaultController::indexAction');
        }

        $token = $request -> get('code', null);
        if ($token == null) {
            $auth_url = $this -> get('google_services') -> createAuthUrl();
            return $this -> redirect($auth_url);
        } else {
            $access_token = $this -> get('google_services') -> authenticateCode($token);
            $session = $request->getSession();
            $session -> set('access_token', $access_token);
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

        $this -> refreshToken($request);
        $messages = $this -> get('google_services') -> messages(10);
        return array("messages" => $messages);
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
        return $session -> get('access_token', null) != null;
    }

    public function refreshToken(Request $request) {
        $session = $request->getSession();
        $access_token = $session -> get('access_token', null);
        $access_token = $this -> get('google_services') -> refreshToken($access_token);
        $session -> set('access_token', $access_token);
    }
}
