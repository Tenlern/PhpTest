<?php

namespace App\Repository;

use App\Entity\News;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method News|null find($id, $lockMode = null, $lockVersion = null)
 * @method News|null findOneBy(array $criteria, array $orderBy = null)
 * @method News[]    findAll()
 * @method News[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, News::class);
    }

    // /**
    //  * @return News[] Returns an array of News objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?News
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     *
     * Метод заполнения сущности после инициализации через конструктор
     *
     * @param string $header Заголовок
     * @param string $synopsis Синопсис/Анонс
     * @return News
     * @throws \Exception
     */
    public function insert(string $header, string $synopsis, string $text, array $tags, string $time): News {
        $manager = $this->getEntityManager();

        $newsArticle = new News();
        $newsArticle->setHeader($header);
        $newsArticle->setSynopsis($synopsis);
        $newsArticle->setText($text);
        $newsArticle->setTags($tags);

        $manager->persist($newsArticle);
        $manager->flush();
        return $newsArticle;
    }

    /**
     * Метод обновления данных объекта
     *
     * @param array $args Словарь полей и новых значений
     * @return $this
     * @throws \Exception
     */
    public function update(int $id, array $args): self {
        $manager = $this->getEntityManager();

        $article = $manager->getRepository(News::class)->find($id);

        foreach ($args as $key => $value) {
            switch ($key) {
                case "header":
                    $article->setHeader($value);
                    break;
                case "synopsis":
                    $article->setSynopsis($value);
                    break;
                case "text":
                    $article->setText($value);
                    break;
                case "tags":
                    $article->setTags($value);
                    break;
            }
        }

        $manager->persist($article);
        $manager->flush();
        return $article;
    }
}
