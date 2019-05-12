<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User extends BaseUser
{
    const SALT = 'Salt';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"user"})
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="News", mappedBy="createdBy", cascade={"persist"})
     * @Assert\Type("object")
     */
    private $news;

    /**
     * @Assert\NotBlank
     * @Assert\Type(
     *     type="string",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @var string
     * @Groups({"user"})
     */
    protected $username;

    /**
     * @Assert\NotBlank
     * @Assert\Type(
     *     type="string",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @var string
     * @Groups({"user"})
     */
    protected $usernameCanonical;

    /**
     * @Assert\NotBlank
     * @Assert\Type(
     *     type="string",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @var string
     * @Groups({"user"})
     */
    protected $email;

    /**
     * @Assert\NotBlank
     * @Assert\Type(
     *     type="string",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @var string
     * @Groups({"user"})
     */
    protected $emailCanonical;

    /**
     * @Assert\NotBlank
     * @Assert\Type(
     *     type="bool",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @var bool
     * @Groups({"user"})
     */
    protected $enabled;

    /**
     * @Assert\NotBlank
     * @Assert\Type(
     *     type="string",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @var string
     * @Groups({"user"})
     */
    protected $salt;

    /**
     * @Assert\NotBlank
     * @Assert\Type(
     *     type="string",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @var string
     * @Groups({"user"})
     */
    protected $password;

    /**
     * @Assert\Type(
     *     type="string",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @var string
     * @Groups({"user"})
     */
    protected $plainPassword;

    /**
     * @var \DateTime|null
     * @Groups({"user"})
     */
    protected $lastLogin;

    /**
     * Random string sent to the user email address in order to verify it.
     *
     * @var string|null
     * @Groups({"user"})
     */
    protected $confirmationToken;

    /**
     * @var \DateTime|null
     * @Groups({"user"})
     */
    protected $passwordRequestedAt;

    /**
     * @var array
     * @Groups({"user"})
     */
    protected $roles;

    public function __construct()
    {
        $this->news = new ArrayCollection();
        parent::__construct();
    }

    /**
     * @return ArrayCollection
     */
    public function getNews(): ArrayCollection
    {
        return $this->news;
    }
}
