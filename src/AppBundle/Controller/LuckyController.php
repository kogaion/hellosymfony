<?php
/**
 * Created by PhpStorm.
 * User: Kogaion
 * Date: 9/14/2017
 * Time: 10:25 AM
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class LuckyController extends Controller
{
    /**
     * @Route("/lucky/number")
     */
    public function numberAction()
    {
        $number = mt_rand(10, 100000);

        //return new Response("<html><body>Lucky number: {$number}</body></html>");
        return $this->render(
            "/lucky/number.html.twig",
            [
                'number' => $number
            ]
        );
    }

}