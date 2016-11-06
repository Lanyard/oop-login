<?php

namespace OopLogin\Test\TestCase\Model;

use OopLogin\Model\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * setUp method.
     */
    public function setUp()
    {
    }

    /**
     * tearDown method.
     */
    public function tearDown()
    {
    }

    /**
     * Test constructor.
     */
    public function testDefaultConstructor()
    {
        /**
         * Once constructed by default, the User
         * should have:
         * - an id
         * - a username
         * - an email
         * - a password
         */

        $user = new User();
        $this->assertEquals($user->getID(), -1);
    }
}
