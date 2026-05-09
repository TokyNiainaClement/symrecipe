<?php

namespace App\Repository;

use App\Entity\Recipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    public function findByIsPublic(?int $nbRecipe = null): array
    {
        $queryBuilder = $this->createQueryBuilder('r')
        ->where('r.isPublic = 1')
        ->orderBy('r.createdAt', 'DESC');

        if($nbRecipe !== 0 || $nbRecipe !== null) {
            $queryBuilder->setMaxResults($nbRecipe);
        }

        return $queryBuilder->getQuery()
        ->getResult();
    }

}
