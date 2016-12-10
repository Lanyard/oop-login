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
    * Test default constructor
    */
    public function testConstructor()
    {
        $this->customUser = new User();
        $this->assertEquals('', $this->customUser->id());
        $this->assertEquals('', $this->customUser->username());
        $this->assertEquals('', $this->customUser->email());
        $this->assertEquals('', $this->customUser->password());
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

    public function testSetId()
    {
        $this->customUser->setId(74);
        $this->assertEquals(74, $this->customUser->id());
    }

    public function testSetUsername()
    {
        $this->customUser->setUsername('owl');
        $this->assertEquals('owl', $this->customUser->username());
    }

    public function testSetEmail()
    {
        $this->customUser->setEmail('food@gmail.com');
        $this->assertEquals('food@gmail.com', $this->customUser->email());
    }

    public function testSetPassword()
    {
        $this->customUser->setPassword('dfsixosnKodp79');
        $this->assertEquals('dfsixosnKodp79', $this->customUser->password());
    }
}
