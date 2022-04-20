<?php

namespace App\Controller;

use App\Entity\UserLogs;
use App\Entity\Users;
use App\Entity\EmailSubsription;
use App\Form\ContentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class SubController extends AbstractController
{

    public function __construct(EntityManagerInterface $em, MailerInterface $mailer)
    {
        $this->entityManager = $em;
        $this->mailer = $mailer;
    }

    public function index(Request $request): Response
    {
        if (!isset($_COOKIE['login']) && $_COOKIE['login'] != 'angen' || $_COOKIE['login'] != 'kuzmin') {
            return $this->redirectToRoute('employees_page');
        }

            $subs = $this->entityManager->getRepository(EmailSubsription::class)->findAll();

            return $this->render('email/index.html.twig',[
                'subs'=>$subs,
                'role'=>$_COOKIE['role'],
                'login'=>$_COOKIE['login']
            ]);


    }

    public function messageForAll(Request $request,EntityManagerInterface $em)
    {
        if (!isset($_COOKIE['login']) && $_COOKIE['login'] != 'angen' || $_COOKIE['login'] != 'kuzmin') {
            return $this->redirectToRoute('employees_page');
        }

        $form = $this->createForm(ContentType::class);
        $form->handleRequest($request);

        if($form->isSubmitted())
        {
            $subject = $form->get('subject')->getData();
            $text = $form->get('text')->getData();
            $errors = array();
            if ($subject == null || $text == null)
            {
                if ($subject == null)
                {
                    array_push($errors,"Введите тему письма");
                }
                if ($text == null)
                {
                    array_push($errors,"Введите текст письма");
                }
                return $this->render('email/forAll.html.twig',[
                    'form'=>$form->createView(),
                    'role' => $_COOKIE['role'],
                    'login' => $_COOKIE['login'],
                    'errors'=>$errors,
                ]);

            }

            $subscribers = $this->entityManager->getRepository(EmailSubsription::class)->findAll();
            $email = (new Email())
                ->from('agataviainfo@gmail.com')
                ->to()
                ->subject($subject);
            foreach ($subscribers as $m)
            {
                $email->addTo($m->getEmail());
            }
            $email->html($text);
                $this->mailer->send($email);

            return $this->redirectToRoute('subs');

        }
        return $this->render('email/forAll.html.twig',[
            'form'=>$form->createView(),
            'role' => $_COOKIE['role'],
            'login' => $_COOKIE['login'],
            'errors'=>null,
        ]);
    }
}
