<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class IngredientController extends AbstractController
{
    /**
     * this controller display all ingredient
     *
     * @param IngredientRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[IsGranted("ROLE_USER")] // restraindre l'accès à la page de liste des
    // ingrédients pour les utilisateur anonyme
    #[Route('/ingredient', name: 'ingredient.index', methods: ['GET'])]
    public function index(
        IngredientRepository $repository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $ingredients = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1), /* Nombre de page */
            5 /* Limite par page */
        );

        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients,
        ]);
    }

    /**
     * This controller show a form which create an ingredient
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[IsGranted("ROLE_USER")] // restraindre l'accès à la page
    // d'ajout de nouveau ingrédient pour les utilisateur anonymes
    #[Route('/ingredient/nouveau', name: 'ingredient.new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $manager
    ): Response {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();

            $ingredient->setUser($this->getUser()); // relier un ingrédient à l'utilisateur
            // courante
            $manager->persist($ingredient);
            $manager->flush();

            // Petit message
            $this->addFlash(
                'success',
                'Votre ingrédient a été crée avec succès !'
            );

            return $this->redirectToRoute('ingredient.index');
        }

        return $this->render('pages/ingredient/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * This controller allow to edit an ingredient
     *
     * @param Ingredient $ingredient
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/ingredient/edition/{id}', name: 'ingredient.edit', methods: ['GET', 'POST'])]
    public function edit(
        Ingredient $ingredient,
        Request $request,
        EntityManagerInterface $manager
    ): Response {

        if($ingredient->getUser() != $this->getUser()) {
            return $this->redirectToRoute('ingredient.index');
        }

        $form = $this->createForm(IngredientType::class, $ingredient); // Utilisation d'un
        // param-converteur => $ingredient

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData(); // Ici $ingrédient est une nouvelle variable
            // déclarée
            $manager->persist($ingredient);
            $manager->flush();

            // Petit message
            $this->addFlash(
                'success',
                'Votre ingrédient a été modifié avec succès !'
            );

            return $this->redirectToRoute('ingredient.index');
        }
        return $this->render('pages/ingredient/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * This controller allow to delete an ingredient
    *
    * @param EntityManagerInterface $manager
    * @param Ingredient $ingredient
    * @return Response
    */
    #[Route('/ingredient/suppression/{id}', name: 'ingredient.delete', methods: ['GET'])]
    public function delete(
        EntityManagerInterface $manager,
        Ingredient $ingredient
    ): Response {
        $manager->remove($ingredient); // $ingredient => param converteur
        $manager->flush();

        // Petit message
        $this->addFlash(
            'success',
            'Votre ingrédient a été supprimé avec succès !'
        );

        return $this->redirectToRoute('ingredient.index');
    }

}
