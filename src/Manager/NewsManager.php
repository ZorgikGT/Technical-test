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

    public function __construct(EntityManagerInterface $emi)
    {
        $this->emi = $emi;
    }

    public function getNewsById(int $id): News
    {
        $news = $this->emi->find(News::class, $id);

        return $news;
    }

    public function pushNews($news)
    {
        $this->emi->persist($news);
        $this->emi->flush();
        $this->emi->clear();
    }
}
