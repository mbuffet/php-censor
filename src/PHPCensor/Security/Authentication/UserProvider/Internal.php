<?php

/**
 * PHPCI - Continuous Integration for PHP
 *
 * @copyright Copyright 2014, Block 8 Limited.
 * @license   https://github.com/Block8/PHPCI/blob/master/LICENSE.md
 * @link      https://www.phptesting.org/
 */

namespace PHPCensor\Security\Authentication\UserProvider;

use PHPCensor\Model\User;
use PHPCensor\Security\Authentication\LoginPasswordProviderInterface;

/**
 * Internal user provider
 * 
 * @author Adirelle <adirelle@gmail.com>
 */
class Internal extends AbstractProvider implements LoginPasswordProviderInterface
{
    public function verifyPassword(User $user, $password)
    {
        return password_verify($password, $user->getHash());
    }

    public function checkRequirements()
    {
        // Always fine
    }

    public function provisionUser($identifier)
    {
        return null;
    }
}
