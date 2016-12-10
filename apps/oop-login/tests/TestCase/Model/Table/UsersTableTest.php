<?php

namespace OopLogin\Test\TestCase\Table;

use OopLogin\Model\Table\UsersTable;
use OopLogin\Model\Entity\User;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use PDO;

class UserTest extends TestCase
{
    private $connection;
    private $usersTable;

    /**
     * setUp method.
     */
    public function setUp()
    {
        $this->connection = new PDO('mysql:host=' . HOST . ';dbname=' . DATABASE, USER, PASSWORD);
        $this->usersTable = new UsersTable($this->connection);
    }

    /**
     * tearDown method.
     */
    public function tearDown()
    {
    }

    /**
     * Test connection getter
     */
    public function testConnection()
    {
        $this->assertEquals($this->connection, $this->usersTable->connection());
    }

    /**
     * Test connection type
     */
    public function testConnectionType()
    {
        $this->expectException(InvalidArgumentException::class);
        $mysqliConnection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);
        $invalidTable = new UsersTable($mysqliConnection);
    }
}
