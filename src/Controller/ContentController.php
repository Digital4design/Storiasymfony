<?php

namespace App\Controller;

use App\Entity\Content;
use App\Form\ContentType;
use App\Repository\ContentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @Route("/content")
 */
class ContentController extends Controller
{
    /**
     * @Route("/", name="content_index", methods={"GET"})
     * 
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(ContentRepository $contentRepository): Response
    {
       
        return $this->render('content/index.html.twig', [
            'contents' => $contentRepository->findAll(),
        ]);
    }

    /**
     * @Route("/list", name="content_list", methods={"GET"})
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
    
    /**
     * @Route("/new", name="content_new", methods={"GET","POST"})
     * 
     * @IsGranted("ROLE_USER")
     */
    public function new(Request $request): Response
    {
        $content = new Content();
        $form = $this->createForm(ContentType::class, $content);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $content->setIsActive(0);
            $entityManager->persist($content);
            $entityManager->flush();

            $session = new Session();

            $session->getFlashBag()->add('notice', 'Blog create successfully.');
        
            return $this->redirectToRoute('content_new');
        }

        return $this->render('content/new.html.twig', [
            'content' => $content,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="content_show", methods={"GET"})
     */
    public function show(Content $content): Response
    {
        return $this->render('content/show.html.twig', [
            'content' => $content,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="content_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Content $content): Response
    {
        $form = $this->createForm(ContentType::class, $content);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('content_index', [
                'id' => $content->getId(),
            ]);
        }

        return $this->render('content/edit.html.twig', [
            'content' => $content,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="content_approve", methods={"POST"})
     *
     * @IsGranted("ROLE_ADMIN") 
     */
    public function approve(Request $request, Content $content,\Swift_Mailer $mailer): Response
    {
        if ($this->isCsrfTokenValid('delete'.$content->getId(), $request->request->get('_token'))) {
            
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
        }

        return $this->redirectToRoute('content_index');
    }
    
}

