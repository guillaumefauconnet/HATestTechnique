<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted(User::ROLE_ADMIN)]
final class UserController extends AbstractController
{
    //#[Route('/', name: 'user_index', defaults: ['page' => '1', '_format' => 'html'], methods: ['GET'])]
    //#[Route('/rss.xml', name: 'user_list_rss', defaults: ['page' => '1', '_format' => 'xml'], methods: ['GET'])]
    #[Route('/', name: 'admin_user_index', defaults: ['_format' => 'html'], methods: ['GET'])]
    public function index(
        EntityManagerInterface $entityManager,
        string $_format
    ): Response {
        $users = $entityManager->getRepository(User::class)->findAll();

        //$latestUser = $entityManager->getRepository(User::class)->findLatest($page, $tag);

        return $this->render('admin/user/index.'.$_format.'.twig', [
            //'paginator' => $latestUser,
            'users' => $users,
        ]);
    }


    /**
     * Creates a new User entity.
     */
    #[Route('/new', name: 'admin_user_new', methods: ['GET', 'POST'])]
    public function new(
        #[CurrentUser] User $user,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = new User();


        // See https://symfony.com/doc/current/form/multiple_buttons.html
        $form = $this->createForm(UserType::class, $user)
            ->add('saveAndCreateNew', SubmitType::class)
        ;

        $form->handleRequest($request);

        // The isSubmitted() call is mandatory because the isValid() method
        // throws an exception if the form has not been submitted.
        // See https://symfony.com/doc/current/forms.html#processing-forms
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            // Flash messages are used to notify the user about the result of the
            // actions. They are deleted automatically from the session as soon
            // as they are accessed.
            // See https://symfony.com/doc/current/controller.html#flash-messages
            $this->addFlash('success', 'user.created.successfully');

            /** @var SubmitButton $submit */
            $submit = $form->get('saveAndCreateNew');

            if ($submit->isClicked()) {
                return $this->redirectToRoute('admin_user_new', [], Response::HTTP_SEE_OTHER);
            }

            return $this->redirectToRoute('admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}
