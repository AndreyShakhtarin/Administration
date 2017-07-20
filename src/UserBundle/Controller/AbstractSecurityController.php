<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 7/19/17
 * Time: 6:55 PM
 */

namespace UserBundle\Controller;

use FOS\UserBundle\Controller\SecurityController;
use Symfony\Component\HttpFoundation\Request;

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