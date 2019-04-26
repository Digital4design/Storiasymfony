<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Content;
use App\Repository\ContentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Service\TokenGenerator;
use JWT\Authentication\JWT;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    
    /**
     * @Route("/login", name="api_login",methods={"POST"})
     */
    public function login(Request $request,UserPasswordEncoderInterface $encoder, JWTTokenManagerInterface $JWTManager)
    {
        $email = trim($request->request->get('email'));
        
        $password = trim($request->request->get('password'));

        
        if(empty($email)){
             return new JsonResponse(['status'=>0,'message'=>'Please enter email']);
        }elseif(empty($password)){
             return new JsonResponse(['status'=>0,'message'=>'Please enter password']);
        }

        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email'=> $email]);

        if (!$user) {
            return new JsonResponse(['status'=>0,'message'=>'user not found']);
        }
        
        $isValid = $encoder->isPasswordValid($user, $password);
                
        if (!$isValid) {
           return new JsonResponse(['status'=>0,'message'=>'invalid password']);
        }
        
        $token = ['token' => $JWTManager->create($user)];
        
        return new JsonResponse(['status'=>1,'message' => $token]);
    }
    
    
    /**
     * @Route("/new", name="api_content_new", methods={"POST"})
     * 
     */
    public function new(Request $request): Response
    {
        
        $user_data =  $this->decodejwt($request);
        
        if(!in_array('ROLE_USER', $user_data['roles'])){
             return new JsonResponse(['status'=>0,'message'=>'Access denied']);
        }
        
        $title = trim($request->request->get('title'));        
        $description = trim($request->request->get('description'));
        $contentdata = trim($request->request->get('content'));
        $email = trim($request->request->get('email'));
        
        if(empty($title)){
             return new JsonResponse(['status'=>0,'message'=>'Please enter title']);
        }elseif(empty($description)){
             return new JsonResponse(['status'=>0,'message'=>'Please enter description']);
        }elseif(empty($contentdata)){
             return new JsonResponse(['status'=>0,'message'=>'Please enter content']);        
        }elseif(empty($email)){
             return new JsonResponse(['status'=>0,'message'=>'Please enter email']);
        }
       
       
        $content = new Content();        
        
        $content->setTitle($title);
        $content->setDescription($description);
        $content->setContent($contentdata);
        $content->setEmail($email);
        
        $entityManager = $this->getDoctrine()->getManager();
        $content->setIsActive(0);
        $entityManager->persist($content);
        $entityManager->flush();
        
        return new JsonResponse(['status'=>1,'message' => 'Content created successfully']);      
    }
    
    /**
     * @Route("/listall", name="api_content_index", methods={"GET"})
     * 
     */
    public function listall(Request $request,ContentRepository $contentRepository): Response
    {
        $user_data =  $this->decodejwt($request);
        
        if(!in_array('ROLE_ADMIN', $user_data['roles'])){
             return new JsonResponse(['status'=>0,'message'=>'Access denied']);
        }
       
          $content_list =  $contentRepository->findAll();
          
          $content_arr=array();
          $i=0;
          foreach($content_list as $content_list_data){
             $content_arr[$i]['title'] = $content_list_data->getTitle();
             $content_arr[$i]['content'] = $content_list_data->getContent();
             $content_arr[$i]['description'] = $content_list_data->getDescription();
             $content_arr[$i]['email'] = $content_list_data->getEmail();
             $content_arr[$i]['is_active'] = $content_list_data->getIsActive();
             $i++;
          }
                  
          return new JsonResponse(['status'=>1,'message' => $content_arr]);      
       
    }

    /**
     * @Route("/show/{id}", name="api_content_show", methods={"GET"})
     */
    public function show($id,Request $request): Response
    {
        $user_data =  $this->decodejwt($request);
        
        if(!in_array('ROLE_ADMIN', $user_data['roles'])){
             return new JsonResponse(['status'=>0,'message'=>'Access denied']);
        }
       
         $contents_val = $this->getDoctrine()->getRepository(Content::class)->findOneBy(['id'=> $id]);
        
         if(empty($contents_val)){
               return new JsonResponse(['status'=>0,'message'=>'content not found']);
         }else{
             
            $result['title'] = $contents_val->getTitle();
            $result['description'] = $contents_val->getDescription();
            $result['content'] = $contents_val->getContent();
            $result['email'] = $contents_val->getEmail();
            $result['is_active'] = $contents_val->getIsActive();
         }
        
        
         return new JsonResponse(['status'=>1,'message'=>$result]);
    }
    
    
     /**
     * @Route("/approve/{id}", name="api_content_approve", methods={"GET"})
      *  
     *
     */
    public function approve($id,Request $request, \Swift_Mailer $mailer): Response
    {
        $user_data =  $this->decodejwt($request);
        
        if(!in_array('ROLE_ADMIN', $user_data['roles'])){
             return new JsonResponse(['status'=>0,'message'=>'Access denied']);
        }
        
        if(!isset($id) || empty($id)){
             return new JsonResponse(['status'=>0,'message'=>'content not found']);
        }
        
         $content = $this->getDoctrine()->getRepository(Content::class)->findOneBy(['id'=> $id]);
        
         if(empty($content)){
               return new JsonResponse(['status'=>0,'message'=>'content not found']);
         }else{
             
            $post_title = $content->getTitle();
            $usermail = $content->getEmail();
            
             $entityManager = $this->getDoctrine()->getManager();
             $content->setIsActive(1);
             $entityManager->persist($content);
             $entityManager->flush();            

             $message = (new \Swift_Message('Post Approved'))
             ->setFrom('malwinder.d4d@gmail.com')
             ->setTo($usermail)
             ->setBody(
                 $this->renderView(
                     'emails/approved.html.twig',
                     ['name' => $usermail,'postname' => $post_title,]
                 ),
                 'text/html'
             );


            $result =  $mailer->send($message); 
             return new JsonResponse(['status'=>1,'message' => 'Content approved successfully']);    
         }       
    }
    
    /**
     * @Route("/list", name="api_list", methods={"GET"})
     * 
     */
    public function list(ContentRepository $contentRepository): Response
    {
      
        $contents = $contentRepository->findBy(['is_active'=>1]);

        $result = [];
        $i=0;
        foreach($contents as $contents_val){
            $result[$i]['title'] = $contents_val->getTitle();
            $result[$i]['description'] = $contents_val->getDescription();
            $result[$i]['content'] = $contents_val->getContent();
            $result[$i]['email'] = $contents_val->getEmail();
            $i++;
        }

        return $this->json($result);
    }    
    
   
    private function decodejwt($request)
    {
        $token = $request->headers->get('Authorization');

        if(empty($token)){
              header('Content-Type: application/json');
             echo json_encode(['status'=>0,'message'=>'user not authenticated']);
             die;
        }
        
        $tokenParts = explode(".", $token);  
        $tokenHeader = base64_decode($tokenParts[0]);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtHeader = json_decode($tokenHeader);
        $jwtPayload = json_decode($tokenPayload);
        
        if(isset($jwtPayload->username) && !empty($jwtPayload->username)){
            
             $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email'=> $jwtPayload->username]);
             
             $user_data['email']= $user->getEmail();
             $user_data['roles']= $user->getRoles();
             
             return $user_data;             
        }else{
            header('Content-Type: application/json');
             echo json_encode(['status'=>0,'message'=>'user not authenticated']);
             die;
        }       
    } 
    
    
}
