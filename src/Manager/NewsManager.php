<?php

namespace App\Manager;


use App\Entity\News;
use Doctrine\ORM\EntityManagerInterface;

class NewsManager
{
    /**
     * @var EntityManagerInterface $emi
     */
    private $emi;

    /**
     * NewsManager constructor.
     * @param EntityManagerInterface $emi
     */
    public function __construct(EntityManagerInterface $emi)
    {
        $this->emi = $emi;
    }

    /**
     * @param int $id
     * @return News
     * @throws \Exception
     */
    public function getNewsById(int $id): News
    {
        /**
         * @var News $news
         */
        $news = $this->emi->find(News::class, $id);

        if(empty($news)) {

            throw new \Exception('News does not exist');
        }

        return $news;
    }

    /**
     * @return object[]
     * @throws \Exception
     */
    public function getAllNews()
    {
        $news = $this->emi
            ->getRepository(News::class)
            ->findAll();

        if(empty($news)) {

            throw new \Exception('No news');
        }

        return $news;
    }

    /**
     * @param News $news
     */
    public function deleteNews(News $news)
    {
        $this->emi->remove($news);
        $this->emi->flush();
        $this->emi->clear();
    }

    /**
     * @param News $news
     */
    public function addNews(News $news)
    {
        $this->emi->persist($news);
        $this->emi->flush();
        $this->emi->clear();
    }
}
