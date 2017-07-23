<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 7/14/17
 * Time: 2:14 PM
 */

namespace UserBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use ConfigurationBundle\Controller\AbstractSecurityController;

use UserBundle\Entity\User;
use UserBundle\Entity\Users;
use UserBundle\Form\EditType;

    

/**
 * Class DefaultController
 * Base controller for Super Admin user.
 * Show all users, show user, create user, edit user, update user, accessed for operation on Super Admin
 * @package UserBundle\Controller
 */
class DefaultController extends AbstractSecurityController
{

    /**
     * Show all user from database on page
     * @param Request $request
     * @param $page
     * @param $sort
     * @param $tag
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showsAction( Request $request, $page, $sort, $tag )
    {

        $admin = $this->getDoctrine()->getRepository( 'UserBundle:User')->findOneByIdAdmin( $this->getUser() );
        $users = $this->getDoctrine( )->getRepository( 'UserBundle:User' )->findByUsers( $admin->getId(), $page, $sort  );

        foreach ( $users as $user )
        {
            $date = $user->getBorn()->format( 'Y-m-d' );
            $user->setBorn( $date );
        }

        $count = (int)(count($users)/7 + 1);


        return $this->render( 'UserBundle:Users:ShowAllUsers/index.html.twig', array(

            'users'         => $users,
            'count'         => $count,
            'current_page'  => $page,
            'sort'          => $sort,
            'tag'           => $tag,
            'data'          => $this->inst( $request ),
        ));
    }

    /**
     * Show Data user
     * @param Request $request
     * @param $token
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction( Request $request, $token )
    {
        $user = $this->getDoctrine( )->getRepository( 'UserBundle:Users' )->findOneByToken( $token );
        if ( ! $user )
        {
            return $this->redirectToRoute( 'homepage' );
        }
        $date = $user->getBorn()->format( 'Y-m-d' );
        $user->setBorn( $date );

        $this->loginAction( $request );

        return $this->render( 'UserBundle:Users:Show/index.html.twig', array(
            'user' => $user,
            'data'  => $this->inst( $request ),
        ));
    }

    /**
     * Create new user
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction( Request $request )
    {
        $em = $this->getDoctrine()->getManager();
        $user = new Users();
        $form = $this->createForm( EditType::class, $user );
        $admin = $em->getRepository('UserBundle:User')->findOneByAdmin( $this->getUser() );
        $form->handleRequest($request);
        if ( $form->isSubmitted() && $form->isValid() ) {
            $user->setToken( );
            $_user = $form->getData();
            $em->persist($admin);
            $em->persist($_user);
            $em->flush();

            return $this->render( 'UserBundle:Users:Create/success.html.twig', array(
                    'user' => $_user,
                ));

        }

        return $this->render( 'UserBundle:Users:Create/index.html.twig', array(
            'form' => $form->createView(),
            'data' => $this->inst( $request ),
        ) );
    }

    /**
     * Edit user
     * @param Request $request
     * @param $token
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction( Request $request, $token )
    {
        $user = $this->getDoctrine( )
            ->getRepository( 'UserBundle:Users' )
            ->findOneByToken( $token );
        if ( ! $user )
        {
            return $this->redirectToRoute( 'homepage' );
        }

        $editForm = $this->createForm( EditType::class, $user, array(
            'action' => $this->generateUrl( 'update_user', [ 'token' => $token ] ),
            'method' => 'PUT',
        ));

        $deleteForm = $this->createDeleteForm( $token );
        $this->loginAction( $request );
        return $this->render( 'UserBundle:Users:Edit/index.html.twig', array(
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'token' => $token,
            'data'  => $this->inst( $request )
        ) );
    }

    /**
     * Create form
     * @param $token
     * @return \Symfony\Component\Form\Form
     */
    private function createDeleteForm( $token )
    {
        return $this->createFormBuilder( [ 'token' => $token ] )
            ->add( 'token', HiddenType::class )
            ->getForm();
    }

    /**
     * Update user
     * @param Request $request
     * @param $token
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateAction( Request $request, $token )
    {
        $em = $this->get( 'doctrine' )
            ->getManager( );
        $user = $this->getDoctrine( )
            ->getRepository( 'UserBundle:Users' )
            ->findOneByToken( $token );
        if ( ! $user )
        {
            return $this->redirectToRoute( 'homepage' );
        }

        $editForm   = $this->createForm( EditType::class, $user, [
            'action' => $this->generateUrl( 'update_user', [ 'token' => $token ] ),
            'method' => 'PUT'
        ] );


        $deleteForm = $this->createDeleteForm( $token );
        $editForm->handleRequest( $request );

        if ( $editForm->isValid( ) ) {

            $em->persist( $user );
            $em->flush( );
            return $this->redirect( $this->generateUrl('show_user', array( 'token' => $token ) ) );
        }

        return $this->render( 'UserBundle:Users:Edit/index.html.twig', array(
            'edit_form'     => $editForm->createView(),
            'delete_form'   => $deleteForm->createView(),
            'token'         => $token,
            'data'          => $this->inst( $request )
        ) );

    }

    /**
     * Delete user
     * @param Request $request
     * @param $token
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction( Request $request, $token )
    {
        $em = $this->get( 'doctrine' )->getManager( );
        $user = $this->getDoctrine( )->getRepository( 'UserBundle:Users' )->findOneByToken( $token );

        if ( ! $user )
        {
            return $this->redirectToRoute( 'homepage' );
        }

        $user_name = $user->getName() . " " . $user->getSurname();
        $em->remove( $user );
        $em->flush();
        return $this->render( "UserBundle:Users:Delete/index.html.twig", array(
            'data' => $this->inst( $request ),
            'user_name' => $user_name,
        ) );
    }
}