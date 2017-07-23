<?php

namespace UserBundle\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use UserBundle\Entity\User;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends \Doctrine\ORM\EntityRepository
{
    public  function findByAll( $page, $sort, $tag )
    {
        $limit = 7;
        $query = $tag != null ? $this->withTag( $page, $sort, $tag, $limit ): $this->withoutTag( $page, $sort, $limit );
        $query_user = $tag != null ? $this->withTagAll( $sort, $tag ): $this->withoutTagAll( );

        $res = array( 'users' => $query->getResult( ), 'all_users' => $query_user->getResult( ) );

        return $res;
    }

    public function findByUser( User $user )
    {
        $data['username'] = $user->getUsername();
        $data['email'] = $user->getEmail();

        foreach ( $data as $field => $value )
        {
            $user_db[ $field ] = $this->createQueryBuilder( 'u' )
                ->select( "u.$field" )
                ->where( "u.$field = :$field" )
                ->setParameter( $field, $value )
                ->getQuery()
                ->getResult();
        }

        return $user_db;
    }

    private function withTag( $page, $sort, $tag, $limit )
    {
        return $query = $this->createQueryBuilder('u')
                ->where( "u.$sort = :$sort")
                ->setParameter( "$sort", $tag )
                ->orderBy("u.$sort")

                ->setFirstResult( $page *  $limit )
                ->setMaxResults( 7 )
                ->getQuery( );
    }

    private function withoutTag( $page, $sort, $limit )
    {
        return $query = $this->createQueryBuilder('u')
                ->orderBy("u.$sort")

                ->setFirstResult( $page *  $limit )
                ->setMaxResults( 7 )
                ->getQuery( );
    }

    private function withoutTagAll()
    {
        return $this->createQueryBuilder( 'u' )
                ->getQuery( );
    }

    private function withTagAll( $sort, $tag )
    {
        return $this->createQueryBuilder( 'u' )
                ->where( "u.$sort = :$sort" )
                ->setParameter( "$sort", $tag )
                ->getQuery( );
    }

    public function findByUsers( $admin, $page = 0, $orderBy = 'name' )
    {

        $id = $this->getEntityManager()
            ->createQuery( 'SELECT u FROM UserBundle:User u WHERE u.username = :username' )
            ->setParameter( 'username', "$admin" )
            ->getResult()[0]
            ->getId()
        ;

        $limit = 7;
        $query = $this->getEntityManager()
            ->createQuery( "SELECT u FROM UserBundle:Users u LEFT JOIN u.user us WHERE us.id = :id ORDER BY u.$orderBy" )
            ->setParameter( 'id', $id )
            ->setMaxResults( $limit )
            ->setFirstResult( $page * $limit)

        ;


        $paginator = new Paginator( $query, $fetchJoinCollection = false );
//        var_dump($paginator);
        return $paginator;
    }
}
