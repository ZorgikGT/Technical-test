<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use PhpParser\Node\Expr\Cast\Object_;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NewsRepository")
 */
class News
{
    /**
     * @var int
     * @SWG\Property(description="The unique identifier of the news.")
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"news"})
     */
    private $id;

    /**
     * @var string
     * @SWG\Property(description="The title.")
     *
     * @Assert\NotBlank
     * @Assert\Type(
     *     type="string",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @ORM\Column(type="string", length=255)
     * @Groups({"news"})
     */
    private $title;

    /**
     * @var string
     * @SWG\Property(description="The description.")
     *
     * @Assert\NotBlank
     * @Assert\Type(
     *     type="string",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @ORM\Column(type="string", length=255)
     * @Groups({"news"})
     */
    private $description;

    /**
     * @var \DateTime
     * @SWG\Property(description="The datetime of the release.")
     *
     * @Assert\DateTime
     * @Assert\NotBlank
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @Assert\NotBlank
     * @ManyToOne(targetEntity="User", inversedBy="news")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     * @Groups({"news"})
     */
    private $createdBy;

    /**
     * News constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return News
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param $description
     * @return News
     */
    public function setDescription($description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return News
     * @throws \Exception
     */
    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime();

        return $this;
    }

    /**
     * @return User
     */
    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    /**
     * @param $user
     * @return News
     */
    public function setCreatedBy($user): self
    {
        $this->createdBy = $user;

        return $this;
    }
}
