<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User extends BaseUser
{
    const SALT = 'Salt';

    /**
     * @var int
     * @SWG\Property(description="The unique identifier of the user.")
     *
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
     * @var string
     * @SWG\Property(description="The username.")
     *
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
     * @var string
     * @SWG\Property(description="The username cononical.")
     *
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
     * @var string
     * @SWG\Property(description="The email adress of the user for communication.")
     *
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
     * @var string
     * @SWG\Property(description="The same thing like email")
     *
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
     * @var bool
     * @SWG\Property(description="User account active or no?")
     *
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
     * @var string
     * @SWG\Property(description="The salt to use for hashing.")
     *
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
     * @var string
     * @SWG\Property(description="Confirm user accessible rights(encrypted).")
     *
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
     * @var string
     * @SWG\Property(description="Plain password. Used for model validation.")
     *
     * @Assert\Type(
     *     type="string",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @var string
     * @Groups({"user"})
     */
    protected $plainPassword;

    /**
     * @var \DateTime
     * @SWG\Property(description="The datetime of the last login.")
     *
     * @var \DateTime|null
     * @Groups({"user"})
     */
    protected $lastLogin;

    /**
     * @var string
     * @SWG\Property(description="Random string sent to the user email address in order to verify it.")
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
     * @var string
     * @SWG\Property(description="User roles.")
     *
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
