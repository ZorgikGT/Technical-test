<?php
/**
 * Created by PhpStorm.
 * User: akter
 * Date: 06.05.19
 * Time: 16:15
 */

namespace App\Manager;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManager as BaseUserManager;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;

class UserManager extends BaseUserManager
{
    public function __construct(
        PasswordUpdaterInterface $passwordUpdater,
        CanonicalFieldsUpdater $canonicalFieldsUpdater
    )
    {
        parent::__construct($passwordUpdater, $canonicalFieldsUpdater);
    }

    public function reloadUser(UserInterface $user)
    {
        // TODO: Implement reloadUser() method.
    }

    public function getClass()
    {
        // TODO: Implement getClass() method.
    }

    public function deleteUser(UserInterface $user)
    {
        // TODO: Implement deleteUser() method.
    }

    public function findUserBy(array $criteria)
    {
        // TODO: Implement findUserBy() method.
    }

    public function findUsers()
    {
        // TODO: Implement findUsers() method.
    }

    public function updateUser(UserInterface $user)
    {
        // TODO: Implement updateUser() method.
    }
}