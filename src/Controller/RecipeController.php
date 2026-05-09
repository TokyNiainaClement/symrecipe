<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class RecipeController extends AbstractController
{
    /**
     * this controller display all recipes
    *
    * @param RecipeRepository $repository
    * @param PaginatorInterface $paginator
    * @param Request $request
    * @return Response
    */
    #[IsGranted('ROLE_USER')]
    #[Route('/recette', name: 'recipe.index', methods: ['GET'])]
    public function index(RecipeRepository $repository, 
    PaginatorInterface $paginator,
    Request $request): Response
    {
        $recipes = $paginator->paginate(
            $repository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1), /* Nombre de page */
            5 /* Limite par page */
        );

        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    /**
     * This controller allow us to see all public recipes
    *
    * @param PaginatorInterface $paginator
    * @param Request $request
    * @param RecipeRepository $repository
    * @return Response
    */
    #[IsGranted('ROLE_USER')]
    #[Route('/recette/public', name: 'recipe.index.public', methods: ['GET'])]
    public function indexPublic(
        PaginatorInterface $paginator,
        Request $request,
        RecipeRepository $repository): Response
    {
        $recipes = $paginator->paginate(
            $repository->findByIsPublic(),
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('pages/recipe/index_public.html.twig', [
            'recipes' => $recipes
        ]);
    }

    /**
     * This controller allow us to see a public recipe
    *
    * @param Recipe $recipe
    * @return Response
    */
    #[IsGranted('ROLE_USER')]
    #[Route('/recette/{id}', name: 'recipe.show', methods: ['GET'])]
    public function show(Recipe $recipe): Response
    {
        if($recipe->isPublic() == false) {
            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('pages/recipe/show.html.twig', [
            'recipe' => $recipe
        ]);
    }

    /**
     * this controller allow us to create a new recipe
    *
    * @param Request $request
    * @param EntityManagerInterface $manager
    * @return Response
    */
    #[IsGranted('ROLE_USER')]
    #[Route('/recette/nouvelle', name: 'recipe.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $recipe->setUser($this->getUser());// Lier une recette à un utilisateur

            $manager->persist($recipe);
            $manager->flush();

            // Petit message
            $this->addFlash(
                'success',
                'Votre recette a été crée avec succès !'
            );

            return $this->redirectToRoute('recipe.index');
        }

        return $this->render('pages/recipe/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * This controller allow us edit a recipe
    *
    * @param Recipe $recipe
    * @param Request $request
    * @param EntityManagerInterface $manager
    * @return Response
    */
    #[IsGranted('ROLE_USER')]
    #[Route('/recette/edition/{id}', name: 'recipe.edit', methods: ['GET', 'POST'])]
    public function edit(
        Recipe $recipe,
        Request $request,
        EntityManagerInterface $manager
    ): Response {

        if($recipe->getUser() != $this->getUser()) {
            return $this->redirectToRoute('recipe.index');
        }
        
        $form = $this->createForm(RecipeType::class, $recipe); // Utilisation d'un
        // param-converteur => $recipe

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData(); // Ici $recipe est une nouvelle variable
            // déclarée
            $manager->persist($recipe);
            $manager->flush();

            // Petit message
            $this->addFlash(
                'success',
                'Votre recette a été modifié avec succès !'
            );

            return $this->redirectToRoute('recipe.index');
        }
        return $this->render('pages/recipe/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * This controller allow us to delete a recipe
    *
     * @param EntityManagerInterface $manager
     * @param Recipe $recipe
     * @return Response
    */
    #[Route('/recette/suppression/{id}', name: 'recipe.delete', methods: ['GET'])]
    public function delete(
        EntityManagerInterface $manager,
        Recipe $recipe
    ): Response {
        $manager->remove($recipe); // $recipe => param converteur
        $manager->flush();

        // Petit message
        $this->addFlash(
            'success',
            'Votre recette a été supprimé avec succès !'
        );

        return $this->redirectToRoute('recipe.index');
    }

}
