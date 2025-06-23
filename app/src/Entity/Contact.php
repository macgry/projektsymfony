<?php

/**
 * Contact entity.
 */

namespace App\Entity;

use App\Entity\Enum\UserRole;
use App\Repository\ContactRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Contact.
 */
#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact extends User implements UserInterface, PasswordAuthenticatedUserInterface
{
}
