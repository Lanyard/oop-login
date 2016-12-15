<?php

namespace OopLogin\Test\TestCase\Table;

use OopLogin\Model\Table\UsersTable;
use OopLogin\Model\Entity\User;
use PHPUnit\Framework\TestCase;
use PHPUnit_Extensions_Database_TestCase;
use InvalidArgumentException;
use PDO;

class UsersTableTest extends PHPUnit_Extensions_Database_TestCase
{
    static private $pdo = null;
    private $connection = null;
    private $usersTable;
    private $dbTable;

    final public function getConnection()
    {
        if ($this->connection === null) {
            if (self::$pdo == null) {
                self::$pdo = new PDO('mysql:host=' . HOST . ';dbname=' . DATABASE, USER, PASSWORD);
            }
            $this->connection = $this->createDefaultDBConnection(self::$pdo, DATABASE);
        }
        return $this->connection;
    }

    /**
     * Retrieve the needed dataset for testing
     */
    public function getDataSet()
    {
        return $this->createMySQLXMLDataSet('tests/Fixture/oop-login.xml');
    }

    /**
     * setUp method.
     */
    public function setUp()
    {
        $this->getConnection();
        $tableName = 'users';

        $this->usersTable = new UsersTable(self::$pdo);
        $dataSet = $this->getDataSet();
        $this->dbTable = $dataSet->getTable($tableName);
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
        $this->assertEquals(self::$pdo, $this->usersTable->connection());
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

    /**
     * Test reading all users
     */
    public function testRead()
    {
        $users = $this->usersTable->read();
        $user = $users[0];
        $id = $user->id;
        $username = $user->username;
        $email = $user->email;
        $password = $user->password;

        $dbId = $this->dbTable->getValue(0, 'id');
        $dbUsername = $this->dbTable->getValue(0, 'username');
        $dbEmail = $this->dbTable->getValue(0, 'email');
        $dbPassword = $this->dbTable->getValue(0, 'password');

        $this->assertEquals($dbId, $id);
        $this->assertEquals($dbUsername, $username);
        $this->assertEquals($dbEmail, $email);
        $this->assertEquals($dbPassword, $password);
    }
}
