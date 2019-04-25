<?php

namespace App\Controller;

use App\Entity\Content;
use App\Repository\ContentRepository;
use Symfony\Component\Security\Core\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Session\Session;

class SiteController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     * @return Response
     */
    function homepage()
    {

        $session = new Session();

        $session->getFlashBag()->add('notice', 'Profile updated');

//        die('www');
//        $user = $this->getUser();
//       print_r($user);
//        $roles = $user->getRoles();
//        print_r($roles);
    //    die('--w--');
     //   var_dump($user->hasRole('ROLE_ADMIN'));
       // die('--');
   
     // var_dump(is_granted('ROLE_USER'));
       // die('checked');
        
// $data = $this->get('session')->get(Security::LAST_USERNAME);
//  $data = $this->get('session')->get('is_admin');
//            
        return $this->render('index.html.twig');
    }
    
    /**
     * @Route("/cronupdate", name="cronupdate")
     * @ParamConverter("get")
     */
    function cronupdate(\Swift_Mailer $mailer)
    {
        $date = new \DateTime();
        $updatedat = clone $date;
        $interval = new \DateInterval("PT5M");
        $interval->invert = 1;
        $date->add($interval);
      
        $entityManager =  $this->getDoctrine()->getEntityManager();
        
         $query = $entityManager->createQuery(
            'SELECT c FROM App\Entity\Content c
            WHERE c.is_active = 0
            AND c.createdAt < :createat
            ')->setParameter('createat', $date);
         
           $content_result =$query->getResult();
          foreach($content_result as $content_resultVal){
             
               $post_title = $content_resultVal->getTitle();
               $usermail = $content_resultVal->getEmail();
               
                $message = (new \Swift_Message('Post Rejected'))
                ->setFrom('malwinder.d4d@gmail.com')
                ->setTo($usermail)
                ->setBody(
                    $this->renderView(
                        'emails/rejected.html.twig',
                        ['name' => $usermail,'postname' => $post_title,]
                    ),
                    'text/html'
                );


           $result =  $mailer->send($message);              
               
          } 

        $query = $entityManager->createQuery(
            'UPDATE App\Entity\Content c
            SET c.is_active = 2 , 
            c.updatedAt = :updatedat
            WHERE c.is_active = 0
            AND c.createdAt < :createat
            ')->setParameter('createat', $date)->setParameter('updatedat', $updatedat);

        $content_result = $query->execute();
        
        die;
       
    }
    
        /**
     * @Route("/mailtest", name="mailtest")
     * @ParamConverter("get")
     */
    function mailtest(\Swift_Mailer $mailer)
    {

        $name= 'Malwidner Singh';
      ///  try {
        $message = (new \Swift_Message('Hello Email'))
        ->setFrom('malwinder.d4d222@gmail.com')
        ->setTo('malwinder.d4d@gmail.com')
        ->setBody(
            $this->renderView(
                // templates/emails/registration.html.twig
                'emails/rejected.html.twig',
                ['name' => $name]
            ),
            'text/html'
        );
     

   $result =  $mailer->send($message);
   
   print_r($result);
 
        die('--hi end--');
       
    }
    
}