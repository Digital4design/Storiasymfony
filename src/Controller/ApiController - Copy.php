<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/login", name="api_login",methods={"GET"})
     */
    public function login(Request $request)
    {
        print_r($request);
        
        
            // But to make it easy to understand ...
        $_username = "malwinder.d4d@gmail.com";
        $_password = "Admin@123";

        // Retrieve the security encoder of symfony
        $factory = $this->get('security.encoders.algorithm');

         $result = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(['email' => $_username]);
         print_r($result);
         die('--w--');
         
        /// Start retrieve user
        // Let's retrieve the user by its username:
        // If you are using FOSUserBundle:
       // $user_manager = $this->get('fos_user.user_manager');
       // $user = $user_manager->findUserByUsername($_username);
        // Or by yourself
        $user = $this->getDoctrine()->getManager()->getRepository("userBundle:User")
                ->findOneBy(array('username' => $_username));
        /// End Retrieve user

        // Check if the user exists !
        if(!$user){
            return new Response(
                'Username doesnt exists',
                Response::HTTP_UNAUTHORIZED,
                array('Content-type' => 'application/json')
            );
        }

        /// Start verification
        $encoder = $factory->getEncoder($user);
        $salt = $user->getSalt();

        if(!$encoder->isPasswordValid($user->getPassword(), $_password, $salt)) {
            return new Response(
                'Username or Password not valid.',
                Response::HTTP_UNAUTHORIZED,
                array('Content-type' => 'application/json')
            );
        } 
        /// End Verification

        // The password matches ! then proceed to set the user in session
        
        //Handle getting or creating the user entity likely with a posted form
        // The third parameter "main" can change according to the name of your firewall in security.yml
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        // If the firewall name is not main, then the set value would be instead:
        // $this->get('session')->set('_security_XXXFIREWALLNAMEXXX', serialize($token));
        $this->get('session')->set('_security_main', serialize($token));
        
        // Fire the login event manually
        $event = new InteractiveLoginEvent($request, $token);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
        
        /*
         * Now the user is authenticated !!!! 
         * Do what you need to do now, like render a view, redirect to route etc.
         */
        return new Response(
            'Welcome '. $user->getUsername(),
            Response::HTTP_OK,
            array('Content-type' => 'application/json')
        );
        
        echo "helo";
        die;
    }
    
    
    /**
    * @Route("/clist", name="api_clist",methods={"GET"})
    */
    public function clist()
    {
        echo "clist";
        die;
    }
    
    
    
}
