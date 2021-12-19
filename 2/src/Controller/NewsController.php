<?php

namespace App\Controller;

use App\Entity\News;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NewsController extends AbstractController
{
    /*
     * @Route("/{id}", name="feed")
     */
    /**
     *
     * Поиск и отображение статьи по id или slug-заголовку
     *
     * @param int $id Ключ новости
     * @param ManagerRegistry $doctrine Объект для работы с сущностями
     * @return Response Объект для View
     */
    public function showArticleByID(int $id, ManagerRegistry $doctrine): Response
    {
        $manager = $doctrine->getManager();

        $article = $manager->getRepository(News::class)->find($id);

        if (!$article) {
            $article = $manager->getRepository(News::class)->findOneBy(['header'=>'id']);
            if (!$article)
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        return $this->render('news.html.twig', [$article]);
    }

    /*
     * @Route("/create")
     */
    /**
     *
     * Метод создания нового объекта
     *
     * @param ManagerRegistry $doctrine Объект для работы с сущностями
     * @return Response Объект для View
     * @throws \Exception
     */
    public function createArticle(ManagerRegistry $doctrine): Response
    {
        $manager = $doctrine->getManager();

        $article = $manager->getRepository(News::class)->insert(
            "Test", "Test", "Test", ["test", "lorem"], "19-12-2021");

        return new Response("ok");
    }

    /*
     * @Route("/update/{id}")
     */
    /**
     *
     * Метод обновления существующего объекта
     *
     * @param int $id Ключ новости
     * @param ManagerRegistry $doctrine Объект для работы с сущностями
     * @param Request $request Инкапсуляция данных запроса
     * @return Response Объект для View
     */
    public function updateArticle(int $id, ManagerRegistry $doctrine, Request $request): Response
    {
        $article = $doctrine->getManager()->getRepository(News::class)->update($id, $request->query->get('data'));

        return $this->render('news.html.twig', [$article]);
    }


    /*
     * @Route("/delete/{id}")
     */
    /**
     * Удаление сущности по id
     * Для данной операции достаточно метода EntityManager
     *
     * @param int $id Ключ новости
     * @param ManagerRegistry $doctrine Объект для работы с сущностями
     * @return Response Объект для View
     */
    public function deleteArticle(int $id, ManagerRegistry $doctrine): Response
    {
        $manager = $doctrine->getManager();

        $article = $manager->getRepository(News::class)->find($id);

        $manager->remove($article);
        $manager->flush();
        return new Response("ok");
    }

}