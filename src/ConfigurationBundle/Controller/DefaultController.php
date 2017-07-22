<?php

namespace ConfigurationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

use ConfigurationBundle\Controller\AbstractSecurityController;


use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Event\FilterUserResponseEvent;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class DefaultController Base setting for working application
 * @package ConfigurationBundle\Controller
 * Base configuration controller
 */
class DefaultController extends AbstractSecurityController
{
    /**
     * Array (last user, current user, token)
     * @var $data array
     */
    private $configs = array( );

    public function indexAction( Request $request )
    {
        return $this->render( 'ConfigurationBundle:Homepage:homepage.html.twig', array(
            'data'  => $this->inst( $request ),
        ));
    }

    /**
     * Start page
     * @param Request $request
     * @param $page
     * @param $sort
     * @param $tag
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function welcomeAction( Request $request, $page, $sort, $tag  )
    {

        $hasAdmin = $this->checkAdmin( );

        if ( $hasAdmin )
        {

            return $this->helperRedirect( $request, $page, $sort, $tag );
        }

        return $this->render( 'ConfigurationBundle:Welcome:index.html.twig', array(
            'data' => $this->inst( $request ),
        ));
    }

    /**
     * Set configuration for parameter.yml
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function dataBaseConfigAction( Request $request )
    {

        $hasAdmin = $this->checkAdmin( );
        if ( $hasAdmin )
        {
            return $this->helperRedirect( $request );
        }

        $configs = $this->getConfigs();
        return $this->render('ConfigurationBundle:DataBase:index.html.twig', array(
            'configs'   => $configs,
            'data'      => $this->inst( $request ),
        ));
    }

    /**
     * Create Super Admin for Application
     * @param Request $request
     * @return null|RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function adminAction( Request $request)
    {
//        $hasAdmin = $this->checkAdmin( );
//        if ( $hasAdmin )
//        {
//            return $this->helperRedirect( $request );
//        }
//        else
//        {
////            $this->setUp();
//        }

        /** @var $formFactory FactoryInterface */
        $formFactory = $this->get('fos_user.registration.form.factory');
        /** @var $userManager UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled( true );
        $user->setSuperAdmin( true );


        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);


                $data = $form->getData();
                $user->setToken();

                $this->setUp( $user->getToken() );

                $userManager->updateUser($user);

                /*****************************************************
                 * Add new functionality (e.g. log the registration) *
                 *****************************************************/
                $this->container->get('logger')->info(
                    sprintf("New user registration: %s", $user)
                );

                if (null === $response = $event->getResponse()) {
                    $url = $this->generateUrl('configuration_confirmed', array( 'page' => $user->getName(), 'sort' => $user->getSurname()));
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                return $response;
            }

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);

            if (null !== $response = $event->getResponse()) {
                return $response;
            }
        }

        return $this->render( 'ConfigurationBundle:Admin:index.html.twig', array(
            'form' => $form->createView(),
            'data' => $this->inst( $request ),
        ) );
    }

    /**
     * Delegate method
     * Create database and create a table in it's.
     * Also create users.
     * @return bool
     */
    public function setUp( $token )
    {
            $this->doCommand( array( 'command' => 'doctrine:database:create' ) );
            $this->doCommand( array( 'command' => 'doctrine:schema:update', '--force' => true ) );
            $this->createUsers( $token );
//            $this->doCommand( array( 'command' => 'doctrine:fixture:load',  'y' ) );

        return true;
    }

    /**
     * Execute command for console
     * @param array $command
     */
    private function doCommand( array $command )
    {
        $kernel = $this->container->get( 'kernel' );

        $application = new Application($kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput( $command  );
        $application->run($input);
    }

    /**
     * Check for Super Admin
     * @return mixed
     */
    private function checkAdmin( )
    {
        $host = $this->getParameter( 'database_host' );
        $name = $this->getParameter( 'database_name' );
        $dbh['user'] = $this->getParameter( 'database_user' );
        $dbh['pass'] = $this->getParameter( 'database_password' );
        $dbh['dbh']  = "mysql:host=$host;dbname=$name";

        $checkTable = $this->get( 'configuration_db' );
        $checkTable->setInit( $dbh );
        $hasAdmin = $checkTable->hasAdmin();

        return $hasAdmin;
    }

    /**
     * Create adn load users to database
     */
    public function createUsers( $token )
    {
        $manager = $this->getDoctrine()->getManager();
        $this->get( 'create_users' )->createUsers( $manager, $token );
    }

    /**
     * Check parameter in parameters.yml for connection to database
     * @return array
     */
    public function getConfigs()
    {
        foreach ( self::PARAMS as $parameter )
        {
            $field = $this->container->getParameter( $parameter );
            $this->replacer( $field, $parameter );
        }

        $this->issetConfig( $this->configs );

        return $this->configs;
    }

    /**
     * Replace '_' on ' ' from $parameter if $field empty
     * @param $field
     * @param $parameter
     * @return mixed|null
     */
    public function replacer( $field, $parameter )
    {
        $config = null;
        if ( !$field  )
        {
            $config = str_replace( '_', ' ', $parameter);
            $this->configs['config'] = false;
            array_push( $this->configs, $config );
        }
        return $config;
    }

    /**
     * Check parameter
     * @param $parameter
     * @return mixed
     */
    public function issetConfig( $parameter )
    {
        if ( empty( $parameter ) )
        {
            $this->configs['config'] = true;
            return $parameter;
        }
    }
}
