<?php

namespace App\Controller\API;

use App\Entity\Ingredient;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class IngredientController extends AbstractController
{
    //liste des ingrédients
    #[Route('/api/ingredients')]
    public function index(IngredientRepository $ingredientRepository): JsonResponse
    {

        $ingredients = $ingredientRepository->findAll();
      
        return $this->json($ingredients, 200, [], [
            'groups'=>['ingredients.index',' categories.index']
        ]);
     }


    //ajout d'un ingrédient
    #[Route('/api/ingredients/create', name: 'ingredient_index', methods:['post'])]
    public function create(EntityManagerInterface $manager, Request $request):JsonResponse
    {
        $ingredient = new Ingredient();
        $ingredient->setName($request->request->get('name'))
                    ->setActive(true)
                    ->setCategory($request->request->get('category'))
                    ;
        $manager->persist($ingredient);
        $manager->flush();

        $data = [
            'id'=>$ingredient->getId(),
            'name'=>$ingredient->getName(),
            'category'=>$ingredient->getCategory(),
            'active'=>$ingredient->isActive()
        ];

        return $this->json($data);

    }

    //vue d'un ingredient
    #[Route('/api/ingredients/show/{id}', requirements:['id'=> Requirement::DIGITS])]
    public function show(Ingredient $ingredient):JsonResponse
    {
        
        return $this->json($ingredient, 200, [], [
            'ingredients.index', 'ingredients.show'
        ]);

    }

    //mise à jour d'un ingredient 
    #[Route('/api/ingredients/update/{id}', requirements:['id'=> Requirement::DIGITS])]
    public function update(EntityManagerInterface $manager, Request $request, SerializerInterface $serializerInterface, int $id):JsonResponse
    {

        $ingredient = $manager->getRepository(Ingredient::class)->find($id);
        $data = $request->getContent();

        if(!$ingredient){
            return $this->json('Pas d\'ingrédient trouvé avec l\'id '.$id, 404);

        } elseif ($ingredient->isActive()) {
            $ingredient->setActive(false);

        } elseif (!$ingredient->isActive()){
            $ingredient->setActive(true);
        }

        $serializerInterface->deserialize($data, Ingredient::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $ingredient]);
        
        $manager->flush($ingredient);


        return $this->json($ingredient);

    }

    //suppression d'un ingredient
    #[Route('/api/ingredients/delete/{id}', methods:['delete'])]
    public function delete(EntityManagerInterface $manager, int $id):JsonResponse
    {
        $ingredient = $manager->getRepository(Ingredient::class)->find($id);
        if(!$ingredient){
            return $this->json('Pas d\'ingrédient trouvé avec l\'id '.$id, 404);
        } else {
            $manager->remove($ingredient);
            $manager->flush();

            return $this->json('L\'ingrédient avec l\'id '.$id.' a été supprimé avec succès');
        }
    }

}
