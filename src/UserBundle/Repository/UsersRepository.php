<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 7/23/17
 * Time: 7:51 AM
 */

namespace UserBundle\Repository;

use UserBundle\Entity\Users;

class UsersRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByUsers( $username )
    {
        /*
         *  select *
            from users
            left join fos_user
            on users.user_id = fos_user.id
            where fos_user.id = 1
            limit 7;
         */
//        $query = $this->getEntityManager()
//            ->createQuery( 'SELECT u FROM UserBundle:Users u LEFT JOIN u.user us WHERE us.id = :id' )
//            ->setParameter( 'id', 2 )
//            ->setMaxResults(7)
//        ;

        $query = $this->createQueryBuilder( 'u' )
            ->select('u')
            ->leftJoin( 'u.user', 'us')
            ->where('us.id = :id')
            ->setParameter('id', 1)
            ->setMaxResults(7)
        ;
        var_dump($query->getQuery());
        return $query->getQuery()->getResult();
    }
}