<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserPasswordType as FormUserPasswordType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    /**
     * This controller allow us to edit user's profile
    *
    * @param User $user
    * @param Request $request
    * @param EntityManagerInterface $manager
    * @return Response
    */
    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $hasher): Response
    {
        // Si aucun utilisateur n'est conncté
        if(!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }

        // Si l'utilisateur connecté est différent de l'utilisateur
        // passé en paramètre (param converteur)
        if($this->getUser() !== $user) {
            return $this->redirectToRoute('recipe.index');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            if($hasher->isPasswordValid($user, $form->getData()->getPlainPassword())) {
                $user = $form->getData();
                $manager->persist($user);
                $manager->flush();
    
                $this->addFlash(
                    'success',
                    'Les informations de votre compte ont bien été modifié.'
                );
    
                return $this->redirectToRoute('recipe.index');

            }
            else {
                $this->addFlash(
                    'warning',
                    'Le mot de passe renseigné est incorrect.'
                );
            }
        }

        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * This controller allow us to edit user's password
    *
    * @param Request $request
    * @param User $user
    * @param UserPasswordHasherInterface $hasher
    * @param EntityManagerInterface $manager
    * @return Response
    */
    #[Route('/utilisateur/edition-mot-de-passe/{id}', name: 'user.edit.password', methods: ['GET', 'POST'])]
    public function editPassword(Request $request,
    User $user, UserPasswordHasherInterface $hasher, EntityManagerInterface $manager): Response
    {
        if(!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }

        if($this->getUser() !== $user) {
            return $this->redirectToRoute('recipe.index');
        }

        $form = $this->createForm(FormUserPasswordType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            if($hasher->isPasswordValid($user, $form->getData()['plainPassword'])) {
                $user->setPassword(
                    $hasher->hashPassword(
                        $user,
                        $form->getData()['newPassword'])
                );

                $manager->persist($user);
                $manager->flush();

                $this->addFlash(
                    'success',
                    'Votre mot de passe a bien été modifié.'
                );

                return $this->redirectToRoute('recipe.index');

            }
            else {
                $this->addFlash(
                    'warning',
                    'Le mot de passe renseigné est incorrect.'
                );
            }
        }

        return $this->render('pages/user/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
