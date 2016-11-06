<?php

namespace OopLogin\Test\TestCase\Model;

use OopLogin\Model\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private $id;
    private $username;
    private $email;
    private $password;
    private $customUser;

    /**
     * setUp method.
     */
    public function setUp()
    {
        $this->id = 1;
        $this->username = 'admin';
        $this->email = 'example@email.com';
        $this->password = 'dfs';

        $this->customUser = new User($this->id, $this->username, $this->email, $this->password);
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
        $this->assertEquals($user->id(), -1);
        $this->assertEquals($user->username(), '');
        $this->assertEquals($user->email(), '');
        $this->assertEquals($user->password(), '');
    }

    /**
    * Test constructor with custom id
    */
    public function testId()
    {
        $this->assertEquals(1, $this->customUser->id());
    }

    /**
    * Test constructor with custom username
    */
    public function testUsername()
    {
        $this->assertEquals('admin', $this->customUser->username());
    }

    /**
    * Test constructor with custom email
    */
    public function testEmail()
    {
        $this->assertEquals('example@email.com', $this->customUser->email());
    }

    /**
    * Test constructor with custom password
    */
    public function testPassword()
    {
        $this->assertEquals('dfs', $this->customUser->password());
    }
}
