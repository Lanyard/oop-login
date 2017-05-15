<?php

namespace OopLogin\Test\TestCase\Table;

use OopLogin\Exception\DuplicateUsernameException;
use OopLogin\Exception\DuplicateEmailException;
use OopLogin\Model\Entity\User;
use OopLogin\Model\Table\UsersTable;
use PDO;
use PHPUnit\Framework\TestCase;
use PHPUnit_Extensions_Database_DataSet_DataSetFilter;
use PHPUnit_Extensions_Database_TestCase;
use DomainException;
use InvalidArgumentException;
use LengthException;

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
    public function getDataSet($set = 'default')
    {
        if ($set == 'default') {
            return $this->createMySQLXMLDataSet('tests/Fixture/oop-login.xml');
        } elseif ($set == 'read-username') {
            return $this->createMySQLXMLDataSet('tests/Fixture/oop-login_read-user-by-username.xml');
        } else {
            return;
        }
    }

    public function insertDataSet($dataSet, $tableName)
    {
        $this->dbTable = $dataSet->getTable($tableName);

        $rowCount = $this->dbTable->getRowCount();

        $stmt = self::$pdo->prepare('INSERT INTO users (username, email, password) VALUES (:username, :email, :password)');

        for ($i = 0; $i < $rowCount; $i++) {
            $row = $this->dbTable->getRow($i);
            $stmt->execute(array(':username' => $row['username'], ':email' => $row['email'], ':password' => $row['password']));
        }
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
        
        $this->insertDataSet($dataSet, $tableName);
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

    /**
     * Test username type
     */
    public function testUsernameType()
    {
        $this->expectException(InvalidArgumentException::class);
        $invalidUsername = 35;
        $newEmail = 'redford@hotmail.com';
        $newPassword = '$2y$10$LTU3sTI5hVvbNhe5FUfMc.HprIuvrkl7RTIX/8j7uBAYw6nPKLpXu';
        $newUser = new User($invalidUsername, $newEmail, $newPassword);
        $this->usersTable->create($newUser);
    }

    /**
     * Test username existence
     */
    public function testUsernameExistence()
    {
        $this->expectException(DomainException::class);
        $invalidUsername = '';
        $newEmail = 'redford@hotmail.com';
        $newPassword = '$2y$10$LTU3sTI5hVvbNhe5FUfMc.HprIuvrkl7RTIX/8j7uBAYw6nPKLpXu';
        $newUser = new User($invalidUsername, $newEmail, $newPassword);
        $this->usersTable->create($newUser);
    }

    /**
     * Test username length
     */
    public function testUsernameLength()
    {
        $this->expectException(LengthException::class);
        $invalidUsername = 'XzGlxOXOX5WBIHwc7uBxQS0p2lYf6XDgSHq2ZPkBliI1bAsNStOE8Gs7onG7FRcqsjuLoeOzFZS5DkP8IWJeqEcvgA4MMx3QqvltsCpPh1IUR5Pn3GMbqQo0K3zluHYmuFBneFH5tRlheZ6tOFFYphU1frUYPUcFSLhoA1JVN5P0DoEHgkZUgDBK21AbyiBHtGTrHCxlIFf1100Jb3svnZ6m750tGhAKpw7l4mrpNZHINlpQWjDTXCJIkoCC4A6Z';
        $newEmail = 'redford@hotmail.com';
        $newPassword = '$2y$10$LTU3sTI5hVvbNhe5FUfMc.HprIuvrkl7RTIX/8j7uBAYw6nPKLpXu';
        $newUser = new User($invalidUsername, $newEmail, $newPassword);
        $this->usersTable->create($newUser);
    }

    /**
     * Test username uniqueness
     */
    public function testUsernameUniqueness()
    {
        $this->expectException(DuplicateUsernameException::class);
        $duplicateUsername = 'someuser';
        $newEmail = 'redford@hotmail.com';
        $newPassword = '$2y$10$LTU3sTI5hVvbNhe5FUfMc.HprIuvrkl7RTIX/8j7uBAYw6nPKLpXu';
        $newUser = new User($duplicateUsername, $newEmail, $newPassword);
        $this->usersTable->create($newUser);
    }

    /**
     * Test email type
     */
    public function testEmailType()
    {
        $this->expectException(InvalidArgumentException::class);
        $newUsername = 'someuser';
        $invalidEmail = 35;
        $newPassword = '$2y$10$LTU3sTI5hVvbNhe5FUfMc.HprIuvrkl7RTIX/8j7uBAYw6nPKLpXu';
        $newUser = new User($newUsername, $invalidEmail, $newPassword);
        $this->usersTable->create($newUser);
    }

    /**
     * Test username existence
     */
    public function testEmailExistence()
    {
        $this->expectException(DomainException::class);
        $newUsername = 'someotheruser';
        $invalidEmail = '';
        $newPassword = '$2y$10$LTU3sTI5hVvbNhe5FUfMc.HprIuvrkl7RTIX/8j7uBAYw6nPKLpXu';
        $newUser = new User($newUsername, $invalidEmail, $newPassword);
        $this->usersTable->create($newUser);
    }

    /**
     * Test username length
     */
    public function testEmailLength()
    {
        $this->expectException(LengthException::class);
        $newUsername = 'someotheruser';
        $invalidEmail = 'XzGlxOXOX5WBIHwc7uBxQS0p2lYf6XDgSHq2ZPkBliI1bAsNStOE8Gs7onG7FRcqsjuLoeOzFZS5DkP8IWJeqEcvgA4MMx3QqvltsCpPh1IUR5Pn3GMbqQo0K3zluHYmuFBneFH5tRlheZ6tOFFYphU1frUYPUcFSLhoA1JVN5P0DoEHgkZUgDBK21AbyiBHtGTrHCxlIFf1100Jb3svnZ6m750tGhAKpw7l4mrpNZHINlpQWjDTXCJIkoCC4A6Z';
        $newPassword = '$2y$10$LTU3sTI5hVvbNhe5FUfMc.HprIuvrkl7RTIX/8j7uBAYw6nPKLpXu';
        $newUser = new User($newUsername, $invalidEmail, $newPassword);
        $this->usersTable->create($newUser);
    }

    /**
     * Test email uniqueness
     */
    public function testEmailUniqueness()
    {
        $this->expectException(DuplicateEmailException::class);
        $newUsername = 'someotheruser';
        $duplicateEmail = 'someuser@domain.com';
        $newPassword = '$2y$10$LTU3sTI5hVvbNhe5FUfMc.HprIuvrkl7RTIX/8j7uBAYw6nPKLpXu';
        $newUser = new User($newUsername, $duplicateEmail, $newPassword);
        $this->usersTable->create($newUser);
    }

    /**
     * Test password type
     */
    public function testPasswordType()
    {
        $this->expectException(InvalidArgumentException::class);
        $newUsername = 'joandoe';
        $newEmail = 'joandoe@domain.org';
        $invalidPassword = 42;
        $newUser = new User($newUsername, $newEmail, $invalidPassword);
        $this->usersTable->create($newUser);
    }

    /**
     * Test password existence
     */
    public function testPasswordExistence()
    {
        $this->expectException(DomainException::class);
        $newUsername = 'joandoe';
        $newEmail = 'joandoe@domain.org';
        $invalidPassword = '';
        $newUser = new User($newUsername, $newEmail, $invalidPassword);
        $this->usersTable->create($newUser);
    }

    /**
     * Test password length
     */
    public function testPasswordLength()
    {
        $this->expectException(LengthException::class);
        $newUsername = 'joandoe';
        $newEmail = 'joandoe@domain.org';
        $invalidPassword = 'XzGlxOXOX5WBIHwc7uBxQS0p2lYf6XDgSHq2ZPkBliI1bAsNStOE8Gs7onG7FRcqsjuLoeOzFZS5DkP8IWJeqEcvgA4MMx3QqvltsCpPh1IUR5Pn3GMbqQo0K3zluHYmuFBneFH5tRlheZ6tOFFYphU1frUYPUcFSLhoA1JVN5P0DoEHgkZUgDBK21AbyiBHtGTrHCxlIFf1100Jb3svnZ6m750tGhAKpw7l4mrpNZHINlpQWjDTXCJIkoCC4A6Z';
        $newUser = new User($newUsername, $newEmail, $invalidPassword);
        $this->usersTable->create($newUser);
    }

    /**
     * Test reading one user by username
     */
    public function testReadUserByUsername()
    {
        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = $this->dbTable->getValue(0, 'id');
        $dbUsername = $this->dbTable->getValue(0, 'username');
        $dbEmail = $this->dbTable->getValue(0, 'email');
        $dbPassword = $this->dbTable->getValue(0, 'password');
        
        $user = $this->usersTable->readByUsername($dbUsername);
        $id = $user->id();
        $username = $user->username();
        $email = $user->email();
        $password = $user->password();

        $this->assertEquals($dbId, $id);
        $this->assertEquals($dbUsername, $username);
        $this->assertEquals($dbEmail, $email);
        $this->assertEquals($dbPassword, $password);
    }

    /**
     * Test reading one user by email
     */
    public function testReadUserByEmail()
    {
        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = $this->dbTable->getValue(1, 'id');
        $dbUsername = $this->dbTable->getValue(1, 'username');
        $dbEmail = $this->dbTable->getValue(1, 'email');
        $dbPassword = $this->dbTable->getValue(1, 'password');

        $user = $this->usersTable->readByEmail($dbEmail);
        $id = $user->id();
        $username = $user->username();
        $email = $user->email();
        $password = $user->password();

        $this->assertEquals($dbId, $id);
        $this->assertEquals($dbUsername, $username);
        $this->assertEquals($dbEmail, $email);
        $this->assertEquals($dbPassword, $password);
    }

    /**
     * Test reading one user by id
     */
    public function testReadUserById()
    {
        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = $this->dbTable->getValue(2, 'id');
        $dbUsername = $this->dbTable->getValue(2, 'username');
        $dbEmail = $this->dbTable->getValue(2, 'email');
        $dbPassword = $this->dbTable->getValue(2, 'password');
        
        $user = $this->usersTable->readById($dbId);
        $id = $user->id();
        $username = $user->username();
        $email = $user->email();
        $password = $user->password();

        $this->assertEquals($dbId, $id);
        $this->assertEquals($dbUsername, $username);
        $this->assertEquals($dbEmail, $email);
        $this->assertEquals($dbPassword, $password);
    }

    /**
     * Test updating one users's username
     */
    public function testUpdateUsername()
    {
        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = $this->dbTable->getValue(2, 'id');
        $dbUsername = $this->dbTable->getValue(2, 'username');
        $dbEmail = $this->dbTable->getValue(2, 'email');
        $dbPassword = $this->dbTable->getValue(2, 'password');

        $newUsername = 'anewmadeupname';

        $this->usersTable->updateUsername($dbId, $newUsername);

        $user = $this->usersTable->readById($dbId);
        $id = $user->id();
        $username = $user->username();
        $email = $user->email();
        $password = $user->password();

        $this->assertEquals($dbId, $id);
        $this->assertEquals($newUsername, $username);
        $this->assertEquals($dbEmail, $email);
        $this->assertEquals($dbPassword, $password);
    }

    /**
     * Test updating nonexistent id
     */
}
