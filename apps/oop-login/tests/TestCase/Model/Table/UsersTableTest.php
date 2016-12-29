<?php

namespace OopLogin\Test\TestCase\Table;

use InvalidArgumentException;
use OopLogin\Model\Entity\User;
use OopLogin\Model\Table\UsersTable;
use PDO;
use PHPUnit\Framework\TestCase;
use PHPUnit_Extensions_Database_DataSet_DataSetFilter;
use PHPUnit_Extensions_Database_TestCase;

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

        $rowCount = $this->dbTable->getRowCount();

        $stmt = self::$pdo->prepare('INSERT INTO users (username, email, password) VALUES (:username, :email, :password)');

        for ($i = 0; $i < $rowCount; $i++) {
            $row = $this->dbTable->getRow($i);
            $stmt->execute(array(':username' => $row['username'], ':email' => $row['email'], ':password' => $row['password']));
        }
    }

    /**
     * tearDown method.
     */
    public function tearDown()
    {
        $stmt = self::$pdo->prepare('TRUNCATE users');
        $stmt->execute();
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
     * Test reading one user
     */
    public function testReadUser()
    {
        $users = $this->usersTable->read();
        $user = $users[0];
        $id = $user->id();
        $username = $user->username();
        $email = $user->email();
        $password = $user->password();

        $dbId = $this->dbTable->getValue(0, 'id');
        $dbUsername = $this->dbTable->getValue(0, 'username');
        $dbEmail = $this->dbTable->getValue(0, 'email');
        $dbPassword = $this->dbTable->getValue(0, 'password');

        $this->assertEquals($dbId, $id);
        $this->assertEquals($dbUsername, $username);
        $this->assertEquals($dbEmail, $email);
        $this->assertEquals($dbPassword, $password);
    }

    /**
     * Test creating one user
     */
    public function testCreateUser()
    {
        /**///$this->markTestSkipped('Don\'t want the table messed with right now.');
        $newUsername = 'robertredford';
        $newEmail = 'redford@hotmail.com';
        $newPassword = '$2y$10$LTU3sTI5hVvbNhe5FUfMc.HprIuvrkl7RTIX/8j7uBAYw6nPKLpXu';
        $newUser = new User($newUsername, $newEmail, $newPassword);
        $this->usersTable->create($newUser);

        $fullDataSet = $this->getConnection()->createDataSet(['users']);
        $dataSet = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($fullDataSet);
        $dataSet->addIncludeTables(['users']);
        $dataSet->setIncludeColumnsForTable('users', ['username', 'email', 'password']);
        $expectedDataSet = $this->createMySQLXMLDataSet('tests/Fixture/oop-login_create-user.xml');
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }
}
