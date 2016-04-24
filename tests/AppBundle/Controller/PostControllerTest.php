<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 4/24/16
 * Time: 2:56 PM
 */

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PostControllerTest extends WebTestCase{

    public function testTrust() {



    }

    public function testShowPost()
    {


        $crawler =(json_encode($user)) $user->request('GET', '/message/1/conversation/2');

        $this->assertGreaterThan(0,
            $crawler->filter('json:contains("")')->count()
        );
    }
} 