<?php

namespace ConfigurationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

use UserBundle\Entity\User;

use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Event\FilterUserResponseEvent;

use FOS\UserBundle\Controller\SecurityController as BaseController;

/**
 * Class DefaultController Base setting for working application
 * @package ConfigurationBundle\Controller
 * Base configuration controller
 */
class DefaultController extends BaseController
{
    /**
     * Array (last user, current user, token)
     * @var $data array
     */
    public static $data;

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
            return $this->renderToHomepage( $request, $page, $sort, $tag );
        }

        return $this->render( 'ConfigurationBundle:Welcome:index.html.twig', array(
            'flag' => 'welcome'
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
            return $this->renderToHomepage( $request );
        }

        $configs = $this->getConfigs();
        return $this->render('ConfigurationBundle:DataBase:index.html.twig', array(
            'configs'   => $configs,
            'flag'      => 'database'
        ));
    }

    /**
     * Create Super Admin for Application
     * @param Request $request
     * @return null|RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function adminAction( Request $request )
    {
        $hasAdmin = $this->checkAdmin( );
        if ( $hasAdmin )
        {
            return $this->renderToHomepage( $request );
        }
        else
        {
            $this->setUp();
        }


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
                $user->setConfirmationToken( sha1( $data->getEmail() . rand( 0, 9999) ));

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
            'flag' => 'admin',
        ) );
    }

    /**
     * Delegate method
     * Create database and create a table in it's.
     * Also create users.
     * @return bool
     */
    public function setUp( )
    {
            $this->doCommand( array( 'command' => 'doctrine:database:create' ) );
            $this->doCommand( array( 'command' => 'doctrine:schema:update', '--force' => true ) );
            $this->createUsers();
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
     * redirect on home page
     * @param $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function renderToHomepage( $request, $page, $sort, $tag)
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
            'data'          => self::$data,
            'current_page'  => $page,
            'users'         => $users['users'],
            'sort'          => $sort,
            'count'         => $count,
            'tag'           => $tag
        ));
    }



    /**
     * Override parent method.
     * @param array $data
     */
    public function renderLogin(array $data)
    {
        if ( ! empty( $data )){
            self::$data = $data;
        }
    }

    /**
     * Override parent method for confirmed
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function confirmedAction()
    {
        return $this->render('UserBundle:Registration:confirmed.html.twig', array(
            'user' => $this->getUser(),
        ));
    }

    /**
     * Create adn load users to database
     */
    public function createUsers()
    {
        $manager = $this->getDoctrine()->getManager();
        $users_male = array( 'Noah', 'Liam', 'William', 'Mason', 'James', 'Benjamin', 'Jacob', 'Michael',  'Elijah', 'Ethan', 'Alexander', 'Oliver', 'Lucas' );
        $users_female = array( 'Emma', 'Olivia', 'Ava', 'Sophia', 'Isabella', 'Mia', 'Charlotte', 'Abigail', 'Emily', 'Harper', 'Amelia', 'Evelyn', 'Elizabeth' );
        $users_surname = array( 'Smith', 'Jones', 'Brown', 'Johnson', 'Williams', 'Miller', 'Taylor', 'Wilson', 'Davis', 'White' );
        $users = array( 0 => $users_female, 1 => $users_male );
        for ( $i = 0; $i < 40; $i++)
        {
            $user = new User();
            $rand = rand( 0, 1 );
            $_users = $users[ $rand ];
            $key_n = array_rand( $_users, 1 );
            $key_l = array_rand( $users_surname, 1 );

            $user->setName( $_users[$key_n] );
            $user->setUsername( $_users[$key_n] . md5($_users[$key_n] . rand(1,99999)));
            $user->setSurname( $users_surname[$key_l]);
            $time_r = rand( -199999999, 1400000000 );
            $born = date('Y-m-d', $time_r);
            $date = new \DateTime($born);
            $user->setConfirmationToken( md5($_users[$key_n] . rand(1,99999)) );
            $user->setBirthday( $date );
            $user->setEnabled( 1 );
            $user->setGender( $rand );
            $user->setEmail( strtolower( $_users[$key_n] ). md5($_users[$key_n] . rand(0, 999999))  . '@gmail.com' );
            $user->setPassword( rand( 1, 1000 ));

            $manager->persist( $user );
            $manager->flush();
        }
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
