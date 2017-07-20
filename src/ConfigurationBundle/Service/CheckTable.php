<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 7/13/17
 * Time: 12:12 PM
 */

namespace ConfigurationBundle\Service;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Check isset table on database
 * Class CheckTable
 * @package ConfigurationBundle\Service
 */
class CheckTable
{

    private $dbh;

    /**
     * Setter for parameters for database
     * @param $dbh
     * @return boolean
     */
    public function setInit( $dbh )
    {
        $this->dbh = $dbh;
    }

    /**
     * Checked in database SuperAdmin
     * @return bool
     */
    public function hasAdmin(  )
    {
        $dns = $this->getConnectDB( $this->dbh );
        if (  $dns ){
            $result = $this->getSuperAdmin( $dns );
            $this->disconnect();
        }
        else
        {
            $result = false;
        }
        return $result;
    }


    /**
     * Forming PDO connection
     * @param $dbh
     * @return \PDO
     */
    private function getConnectDB( $dbh )
    {
        try
        {
            $dns = new \PDO( $dbh['dbh'], $dbh['user'], $dbh['pass'] );
        }
        catch (\PDOException $exception )
        {
            $dns = false;
        }


        return $dns;
    }

    /**
     * Checks isset table and Super Admin in database
     * @param $dns
     * @return bool
     */
    private function getSuperAdmin( $dns )
    {

        $sql = "SELECT *
                FROM fos_user
                WHERE roles = ?";

        $admin = 'a:1:{i:0;s:16:"ROLE_SUPER_ADMIN";}';

        try {
            $stmt = $dns->prepare( $sql );
            if ( $stmt->execute( array( $admin )) )
            {
                while ( $row = $stmt->fetch() )
                {
                    $rows  = $row;
                }
                if ( ! empty( $rows ))
                {
                    return true;
                }
            }
        }catch ( Exception $exception)
        {
            return false;
        }

    }

    /**
     * Disconnect connection with database
     * @return null
     */
    private function disconnect()
    {
        $this->dbh =null;
        $stmt = null;

        return null;
    }
}