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
     * Redirect for routes with registration
     * @param $request
     * @param $name
     * @param $sort
     * @param $tag
     * @return null|RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    protected function helperRedirect( $request, $name = null, $sort = null, $tag = null )
    {
        switch ( $sort ) {
            case $sort == 'check-email' :
                return $this->checkEmail($request);
            case $sort == 'confirm' :
                return $this->confirm($request, $tag);
            case $sort == 'confirmed' :
                return $this->confirmed();
            default : return $this->homepage( $request, $name, $sort, $tag );
        }

    }

    /**
     * redirect on home page
     * @param $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function homepage( $request, $page, $sort, $tag )
    {

        $this->loginAction( $request );

        $users = $this->getDoctrine( )->getRepository( 'UserBundle:User')->findByAll( $page, $sort, $tag );
        $count = (int)(count($users['all_users'])/7 + 1);

        foreach ( $users['users'] as $user )
        {
            $date = $user->getBirthday()->format( 'Y-m-d' );
            $user->setBirthday( $date );
        }


        return $this->render('UserBundle:Default:index.html.twig', array(
            'data'          => $this->inst( $request),
            'current_page'  => $page,
            'users'         => $users['users'],
            'sort'          => $sort,
            'count'         => $count,
            'tag'           => $tag
        ));
    }




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