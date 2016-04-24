<?php

/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 4/23/16
 * Time: 4:09 PM
 */

namespace AppBundle\Controller;


use AppBundle\Entity\User;
use AppBundle\Entity\Conversation;
use Doctrine\DBAL\Driver\PDOException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\DateType;


class ChartController extends Controller{

    /**
     * @Route("/")
     */

    public function showAction(){

      $link="/conversation/1/message";




        return $this->render('chat/start.html.twig',['link'=>$link]);


    }
    /**
     * @Route("/user/{userid}/conversation/{conversationid}")
     * @Method("GET")
     */

    public function firsEndPointAction($userid, $conversationid){
        function regex_check($data){
            return (preg_match('/^\d+$/',$data));
        };
        if  (!regex_check($conversationid))
            return new Response('Error in conversation:'.$conversationid,404);
        if  (!regex_check($userid))
            return new Response('Error in conversation:'.$userid,404);


// DB connection
        $DB_HOST=$this->container->getParameter('database_host');
        $DB_USERNAME=$this->container->getParameter('database_user');
        $DB_PASS=$this->container->getParameter('database_password');
        $DB_NAME=$this->container->getParameter('database_name');

        $link = mysqli_connect($DB_HOST , $DB_USERNAME, $DB_PASS, $DB_NAME);
        mysqli_set_charset($link,'utf8');

//        $sql= "select  con.id, con.create_at, con.message_count, ms.text, ms.created_at, ms.sender, ms.receiver FROM message AS ms ".
//            " INNER JOIN conversation AS con ".
//            " ON ms.conversation_id = con.id ".
//            " WHERE con.creator_id=$userid AND con.id=$conversationid ";//bad practice need to change

        $sql = "SELECT id, create_at, message_count FROM conversation".
               " WHERE creator_id=$userid ";

        $sql1 = "SELECT  sender, receiver, created_at, text FROM message".
            " WHERE conversation_id=$conversationid AND (sender=$userid OR receiver=$userid) ORDER BY created_at DESC";

        $result_conv = mysqli_query($link,$sql);
        $result_mess = mysqli_query($link,$sql1);

        if (!$result_conv OR !$result_mess) {
            http_response_code(404);
            die(mysqli_error($link));
        }

        $conversation=mysqli_fetch_object($result_conv);//only 1 object as we send conversation_id =id in conversation

        $messages=array();
         while ($row=mysqli_fetch_object($result_mess)){

             array_push($messages,$row);
        }
        $object = (object) [
            'conversation' => ($messages ? $conversation:null),
            'messages' => ($conversation ?  $messages:null)
        ];
          return new Response(json_encode($object,true));


    }
    /**
     * @Route("/conversation/{conversationid}/message")
     * @Method("POST")
     */

    public function secondEndPointAction($conversationid){
        function test_input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        };
        function regex_check($data){
             return (preg_match('/^\d+$/',$data));
                };
        $conversationid = test_input($conversationid);
        $sender_id = test_input($_POST['sender_id']);
        $receiver_id =test_input( $_POST['receiver_id']);
        $text = test_input($_POST['text']);

        if  (!regex_check($conversationid))
            return new Response('Error in conversation:'.$conversationid,404);
        if (!regex_check(regex_check($sender_id)))
            return new Response('Error in sender id:'.$sender_id,404);
        if (!regex_check(regex_check($receiver_id)))
            return new Response('Error in recever id:'.$receiver_id,404);
//
//        echo "sender_id:";
//        print_r($sender_id);
//        echo "\n";
//        print_r($receiver_id);
//        echo "\n";
//        print_r($text);
//        echo "\n";
       //  $columns = preg_replace('/[^a-z0-9_]+/i','',array_keys($input));
        $DB_HOST=$this->container->getParameter('database_host');
        $DB_USERNAME=$this->container->getParameter('database_user');
        $DB_PASS=$this->container->getParameter('database_password');
        $DB_NAME=$this->container->getParameter('database_name');

        $link = mysqli_connect($DB_HOST , $DB_USERNAME, $DB_PASS, $DB_NAME);
        mysqli_set_charset($link,'utf8');

        //cheching users present in other way constraint will evoke
        $sql="SELECT * FROM user WHERE".
            " id=$sender_id ";
       $result_conv = mysqli_query($link,$sql);
        if (!$result_conv) {
            return new Response(mysqli_error($link),404);
        }else
        {      $user=mysqli_fetch_object($result_conv);//only 1 object as we send user_id =id in user
            if (is_null($user)  ) //no user
                return new Response("No such user",404);
        }
        //end checking user
        //cheching receiver(users) present in other way constraint will evoke
        $sql="SELECT * FROM user WHERE".
            " id=$receiver_id ";
        $result_conv = mysqli_query($link,$sql);
        if (!$result_conv) {
            return new Response(mysqli_error($link),404);
        }else
        {      $user=mysqli_fetch_object($result_conv);//only 1 object as we send user_id =id in user
            if (is_null($user)  ) //no user
                return new Response("No such receiver",404);
        }
        //end checking user
            $sql="SELECT * FROM conversation WHERE".
            " id=$conversationid ";


        $result_conv = mysqli_query($link,$sql);
                if (!$result_conv) {
                return new Response(mysqli_error($link),404);
        }else

        $conversation=mysqli_fetch_object($result_conv);//only 1 object as we send conversation_id =id in conversation

        //


        $sql="SELECT * FROM conversation WHERE".
            " id=$conversationid ";


        $result_conv = mysqli_query($link,$sql);
                if (!$result_conv) {
                return new Response(mysqli_error($link),404);
        }else

        $conversation=mysqli_fetch_object($result_conv);//only 1 object as we send conversation_id =id in conversation



        if (is_null($conversation)  ) {// Insert new conversation
        //echo "insert new conversation";//begin
            $sql = "BEGIN; ".
                  "INSERT INTO conversation ".
                " (id,creator_id,create_at,message_count) ".
                " VALUES ($conversationid,$sender_id,NOW(), 1 ); ";

        //echo "insert new conversation";//end
        } else { //Update conversation
          //echo "update conversation";//begin
            $sql = "BEGIN; ".
                    "UPDATE conversation SET ".
                   "message_count=message_count+1  ".
                   "WHERE id=$conversationid ;";

          //echo "update conversation";//end
        }
        $sql .= " INSERT INTO message ".
            " (id,text,created_at,sender,receiver,conversation_id) ".
            " VALUES ".
            "( 0,'$text',NOW(),$sender_id,$receiver_id,$conversationid) ;".
            " COMMIT";
        //echo "\n $sql \n";

        $result_conv = mysqli_multi_query($link,$sql);

        if (!$result_conv) {
            return new Response(mysqli_error($link),404);
        }



//


        return new Response('successful :HTTP/1.1 201 Created',201);

    }

}
//If a resource has been created on the origin server, the response SHOULD be 201 (Created)
//and contain an entity which describes the status of the request and refers to the new resource,
//and a Location header (see section 14.30).