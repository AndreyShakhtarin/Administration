<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 7/19/17
 * Time: 6:55 PM
 */

namespace ConfigurationBundle\Controller;

use FOS\UserBundle\Controller\SecurityController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FilterUserResponseEvent;

/**
 * Class AbstractSecurityController call loginAction
 * from FOS\UserBundle\Controller\SecurityController
 * override parent method renderLogin()
 * @package UserBundle\Controller
 */
class AbstractSecurityController extends SecurityController
{
    protected $data;

    /**
     * Array (last user, current user, token)
     * @var $data array
     */
    private $configs = array( );

    const  PARAMS = array(
        'database_host',
        'database_name',
        'database_user',
        'database_password',
        'mailer_transport',
        'mailer_host',
        'mailer_user',
        'mailer_password'
    );

    /**
     * Redirect to confirmed page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmed()
    {
        return $this->render('UserBundle:Registration:confirmed.html.twig', array(
            'user' => $this->getUser(),
        ));
    }

    /**
     * Call parent method FOS\UserBundle\Controller\SecurityController loginAction().
     * @param Request $request
     * @return mixed
     */
    public function inst(Request $request)
    {
        $this->loginAction($request);

        return $this->data;
    }

    /**
     * Override parent method FOS\UserBundle\Controller\SecurityController renderLogin()
     * @param array $data
     */
    public function renderLogin(array $data)
    {
        $this->data = $data;
    }
}