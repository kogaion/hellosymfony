<?php
/**
 * Created by PhpStorm.
 * User: Kogaion
 * Date: 9/15/2017
 * Time: 12:58 PM
 */

namespace CourseraBundle\Presentation\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public function loginAction()
    {
        return new Response("<html><body>trying to login with: ".$this->getParameter("app.coursera_username")."</body></html>");
    }

}