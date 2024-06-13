<?php

namespace App\Controller\API;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{

    #[Route('/api/categories')]
    public function index(CategoryRepository $categoryRepository): JsonResponse
    {

        $categories = $categoryRepository->findAllCatWithIngredients();

        foreach ($categories as $category) {
            $category->getIngredients()->initialize();
        }

       
        return $this->json($categories, 200, [], [
            'groups'=>['categories.index', 'ingredients.index']
        ]);
     }

      //ajout d'une categorie
    #[Route('/api/categories/create', name: 'categories_index', methods:['post'])]
    public function create(EntityManagerInterface $manager, Request $request):JsonResponse
    {
        $category = new Category();
        $category->setName($request->request->get('name'))
                    ->setActive(true);
                    ;
        $manager->persist($category);
        $manager->flush();

        $data = [
            'id'=>$category->getId(),
            'name'=>$category->getName(),
            'active'=>$category->isActive()
        ];

        return $this->json($data);

    }

    
    //vue d'un categorie
    #[Route('/api/categories/show/{id}', requirements:['id'=> Requirement::DIGITS])]
    public function show(Category $category):JsonResponse
    {
        
        return $this->json($category, 200, [], [
             'categories.show'
        ]);

    }

    //mise à jour d'une categorie 
    #[Route('/api/categories/update/{id}', requirements:['id'=> Requirement::DIGITS])]
    public function update(EntityManagerInterface $manager, Request $request, SerializerInterface $serializerInterface, int $id):JsonResponse
    {

        $category = $manager->getRepository(Category::class)->find($id);
        $data = $request->getContent();

        if(!$category){
            return $this->json('Pas d\'ingrédient trouvé avec l\'id '.$id, 404);

        } elseif ($category->isActive()) {
            $category->setActive(false);

        } elseif (!$category->isActive()){
            $category->setActive(true);
        }

        $serializerInterface->deserialize($data, Category::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $category]);
        
        $manager->flush($category);


        return $this->json($category);

    }

    #[Route('/api/categories/delete/{id}', methods:['delete'])]
    public function delete(EntityManagerInterface $manager, int $id):JsonResponse
    {
        $category = $manager->getRepository(Category::class)->find($id);
        if(!$category){
            return $this->json('Pas d\'ingrédient trouvé avec l\'id '.$id, 404);
        } else {
            $manager->remove($category);
            $manager->flush();

            return $this->json('L\'ingrédient avec l\'id '.$id.' a été supprimé avec succès');
        }
    }

}
