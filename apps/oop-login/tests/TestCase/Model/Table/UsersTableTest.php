<?php

namespace OopLogin\Test\TestCase\Table;

use OopLogin\Exception\DuplicateUsernameException;
use OopLogin\Exception\DuplicateEmailException;
use OopLogin\Exception\NotFoundException;
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

        $dbId = (int) $this->dbTable->getValue(0, 'id');
        $dbUsername = $this->dbTable->getValue(0, 'username');
        $dbEmail = $this->dbTable->getValue(0, 'email');
        $dbPassword = $this->dbTable->getValue(0, 'password');

        $this->assertEquals($dbId, $id);
        $this->assertEquals($dbUsername, $username);
        $this->assertEquals($dbEmail, $email);
        $this->assertEquals($dbPassword, $password);
    }

    /**
     * Validate returned id type when reading users
     */
    public function testReadUserIdType()
    {
        $users = $this->usersTable->read();
        $user = $users[0];
        $id = $user->id();

        $this->assertEquals(true, is_int($id));
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
     * Test username emptiness
     */
    public function testUsernameEmpty()
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
     * Test username emptiness
     */
    public function testEmailEmpty()
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
     * Test password emptiness
     */
    public function testPasswordEmpty()
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

        $dbId = (int) $this->dbTable->getValue(0, 'id');
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
     * Test returned id type when reading user by username
     */
    public function testReadUserByUsernameIdType()
    {
        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(0, 'id');
        $dbUsername = $this->dbTable->getValue(0, 'username');
        $dbEmail = $this->dbTable->getValue(0, 'email');
        $dbPassword = $this->dbTable->getValue(0, 'password');
        
        $user = $this->usersTable->readByUsername($dbUsername);
        $id = $user->id();

        $this->assertEquals(true, is_int($id));
    }

    /**
     * Test username type validation when reading by username
     */
    public function testReadUserByUsernameType()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidUsername = 35;

        $this->usersTable->readByUsername($invalidUsername);
    }

    /**
     * Test username emptiness validation when reading by username
     */
    public function testReadUserByUsernameEmpty()
    {
        $this->expectException(DomainException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidUsername = '';

        $this->usersTable->readByUsername($invalidUsername);
    }

    /**
     * Test username length validation when reading by username
     */
    public function testReadUserByUsernameLength()
    {
        $this->expectException(LengthException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidUsername = 'XzGlxOXOX5WBIHwc7uBxQS0p2lYf6XDgSHq2ZPkBliI1bAsNStOE8Gs7onG7FRcqsjuLoeOzFZS5DkP8IWJeqEcvgA4MMx3QqvltsCpPh1IUR5Pn3GMbqQo0K3zluHYmuFBneFH5tRlheZ6tOFFYphU1frUYPUcFSLhoA1JVN5P0DoEHgkZUgDBK21AbyiBHtGTrHCxlIFf1100Jb3svnZ6m750tGhAKpw7l4mrpNZHINlpQWjDTXCJIkoCC4A6Z';

        $this->usersTable->readByUsername($invalidUsername);
    }

    /**
     * Test reading one user by email
     */
    public function testReadUserByEmail()
    {
        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(1, 'id');
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
     * Test returned id type when reading user by email
     */
    public function testReadUserByEmailIdType()
    {
        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(1, 'id');
        $dbUsername = $this->dbTable->getValue(1, 'username');
        $dbEmail = $this->dbTable->getValue(1, 'email');
        $dbPassword = $this->dbTable->getValue(1, 'password');

        $user = $this->usersTable->readByEmail($dbEmail);
        $id = $user->id();

        $this->assertEquals(true, is_int($id));
    }

    /**
     * Test email type validation while reading by email
     */
    public function testReadUserByEmailType()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidEmail = 35;

        $user = $this->usersTable->readByEmail($invalidEmail);
    }

    /**
     * Test email emptiness validation while reading by email
     */
    public function testReadUserByEmailEmpty()
    {
        $this->expectException(DomainException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidEmail = '';

        $user = $this->usersTable->readByEmail($invalidEmail);
    }

    /**
     * Test email length validation while reding by email
     */
    public function testReadUserByEmailLength()
    {
        $this->expectException(LengthException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidEmail = 'XzGlxOXOX5WBIHwc7uBxQS0p2lYf6XDgSHq2ZPkBliI1bAsNStOE8Gs7onG7FRcqsjuLoeOzFZS5DkP8IWJeqEcvgA4MMx3QqvltsCpPh1IUR5Pn3GMbqQo0K3zluHYmuFBneFH5tRlheZ6tOFFYphU1frUYPUcFSLhoA1JVN5P0DoEHgkZUgDBK21AbyiBHtGTrHCxlIFf1100Jb3svnZ6m750tGhAKpw7l4mrpNZHINlpQWjDTXCJIkoCC4A6Z';

        $user = $this->usersTable->readByEmail($invalidEmail);
    }

    /**
     * Test reading one user by id
     */
    public function testReadUserById()
    {
        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(2, 'id');
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
     * Test returned id type when reading one user by id
     */
    public function testReadUserByIdIdType()
    {
        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(2, 'id');
        $dbUsername = $this->dbTable->getValue(2, 'username');
        $dbEmail = $this->dbTable->getValue(2, 'email');
        $dbPassword = $this->dbTable->getValue(2, 'password');
        
        $user = $this->usersTable->readById($dbId);
        $id = $user->id();

        $this->assertEquals(true, is_int($id));
    }

    /**
     * Test id type reading user by id
     */
    public function testReadUserByIdType()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidId = 'somekindofid';
        $user = $this->usersTable->readById($invalidId);
    }

    /**
     * Test id value when reading user by id
     */
    public function testReadUserByIdValue()
    {
        $this->expectException(DomainException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidId = -1;
        $user = $this->usersTable->readById($invalidId);
    }

    /**
     * Test updating one user's username
     */
    public function testUpdateUsername()
    {
        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(2, 'id');
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
     * Test id type updating username
     */
    public function testUpdateUsernameIdType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidId = 'whatever';
        $newUsername = 'anewmadeupname';

        $this->usersTable->updateUsername($invalidId, $newUsername);
    }

    /**
     * Test id domain validation when updating username
     */
    public function testUpdateUsernameIdValue()
    {
        $this->expectException(DomainException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidId = -5;
        $newUsername = 'anewmadeupname';

        $this->usersTable->updateUsername($invalidId, $newUsername);
    }

    /**
     * Test id existence in db when updating username
     */
    public function testUpdateUsernameIdExistence()
    {
        $this->expectException(NotFoundException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidId = 459;
        $newUsername = 'anewmadeupname';

        $this->usersTable->updateUsername($invalidId, $newUsername);
    }

    /**
     * Test username type validation when updating username
     */
    public function testUpdateUsernameType()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(2, 'id');
        $invalidUsername = 35;

        $this->usersTable->updateUsername($dbId, $invalidUsername);
    }

    /**
     * Test username emptiness validation when updating username
     */
    public function testUpdateUsernameEmpty()
    {
        $this->expectException(DomainException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(2, 'id');
        $invalidUsername = '';

        $this->usersTable->updateUsername($dbId, $invalidUsername);
    }

    /**
     * Test username length validation when updating username
     */

    public function testUpdateUsernameLength()
    {
        $this->expectException(LengthException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(2, 'id');
        $invalidUsername = 'XzGlxOXOX5WBIHwc7uBxQS0p2lYf6XDgSHq2ZPkBliI1bAsNStOE8Gs7onG7FRcqsjuLoeOzFZS5DkP8IWJeqEcvgA4MMx3QqvltsCpPh1IUR5Pn3GMbqQo0K3zluHYmuFBneFH5tRlheZ6tOFFYkdNOSokfrUYPUcFSLhoA1JVN5P0DoEHgkZUgDBK21AbyiBHtGTrHCxlIFf1100Jb3svnZ6m750tGhAKpw7l4mrpNZHINlpQWjDTXCJIkoCC4A6Z';

        $this->usersTable->updateUsername($dbId, $invalidUsername);
    }

    /**
     * Test for duplicate username when updating username
     */
    public function testUpdateUsernameDuplicate()
    {
        $this->expectException(DuplicateUsernameException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(2, 'id');
        $duplicateUsername = $this->dbTable->getValue(1, 'username');

        $this->usersTable->updateUsername($dbId, $duplicateUsername);
    }

    /**
     * Test updating User's email
     */
    public function testUpdateEmail()
    {
        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(2, 'id');
        $newEmail = 'whogivesaflyingcircus@gmail.com';

        $this->usersTable->updateEmail($dbId, $newEmail);

        $user = $this->usersTable->readById($dbId);
        $email = $user->email();

        $this->assertEquals($newEmail, $email);
    }

    /**
     * Test email type validation when updating User's email
     */
    public function testUpdateEmailType()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(2, 'id');
        $invalidEmail = 123;

        $this->usersTable->updateEmail($dbId, $invalidEmail);
    }

    /**
     * Test email emptiness validation when updating User's email
     */
    public function testUpdateEmailEmpty()
    {
        $this->expectException(DomainException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(2, 'id');
        $invalidEmail = '';

        $this->usersTable->updateEmail($dbId, $invalidEmail);
    }

    /**
     * Test email length validation when updating User's email
     */
    public function testUpdateEmailLength()
    {
        $this->expectException(LengthException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(2, 'id');
        $invalidEmail = 'XzGlxOXOX5WBIHwc7uBxQS0p2lYf6XDgSHq2ZPkBliI1bAsNStOE8Gs7onG7FRcqsjuLoeOzFZS5DkP8IWJeqEcvgA4MMx3QqvltsCpPh1IUR5Pn3GMbqQo0K3zluHYmuFBneFH5tRlheZ6tOFFYphU1frUYPUcFSLhoA1JVN5P0DoEHgkZUgDBK21AbyiBHtGTrHCxlIFf1100Jb3svnZ6m750tGhAKpw7l4mrpNZHINlpQWjDTXCJIkoCC4A6Z';

        $this->usersTable->updateEmail($dbId, $invalidEmail);
    }

    /**
     * Test duplicate email checking when updating User's email
     */
    public function testUpdateEmailDuplicate()
    {
        $this->expectException(DuplicateEmailException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(2, 'id');
        $duplicateEmail = $this->dbTable->getValue(1, 'email');

        $this->usersTable->updateEmail($dbId, $duplicateEmail);
    }

    /**
     * Test id type validation when updating User's email
     */
    public function testUpdateEmailIdType()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = $this->dbTable->getValue(2, 'id');
        $newEmail = 'whogivesaflyingcircus@gmail.com';

        $this->usersTable->updateEmail($dbId, $newEmail);
    }

    /**
     * Test id domain validation when updating email
     */
    public function testUpdateEmailIdValue()
    {
        $this->expectException(DomainException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidId = -5;
        $newEmail = 'whogivesaflyingcircus@gmail.com';

        $this->usersTable->updateEmail($invalidId, $newEmail);
    }

    /**
     * Test id existence when updating email
     */
    public function testUpdateEmailIdExistence()
    {
        $this->expectException(NotFoundException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidId = 349;
        $newEmail = 'whogivesaflyingcircus@gmail.com';

        $this->usersTable->updateEmail($invalidId, $newEmail);
    }

    /**
     * Test updating User's password
     */
    public function testUpdatePassword()
    {
        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(2, 'id');
        $newPassword = '$2y$10$Dm9oI/pqrr1706eOPcehLeTnWZ8f0iJVR02WEPsGr8N5muXUrBkRK';

        $this->usersTable->updatePassword($dbId, $newPassword);

        $user = $this->usersTable->readById($dbId);

        $password = $user->password();

        $this->assertEquals($newPassword, $password);
    }

    /**
     * Test password type validation when updating password
     */
    public function testUpdatePasswordType()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(2, 'id');
        $invalidPassword = 25;

        $this->usersTable->updatePassword($dbId, $invalidPassword);
    }

    /**
     * Test password emptiness validation when updating password
     */
    public function testUpdatePasswordEmpty()
    {
        $this->expectException(DomainException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(2, 'id');
        $invalidPassword = '';

        $this->usersTable->updatePassword($dbId, $invalidPassword);
    }

    /**
     * Test password length validation when updating password
     */
    public function testUpdatePasswordLength()
    {
        $this->expectException(LengthException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');


        $dbId = (int) $this->dbTable->getValue(2, 'id');
        $invalidPassword = 'XzGlxOXOX5WBIHwc7uBxQS0p2lYf6XDgSHq2ZPkBliI1bAsNStOE8Gs7onG7FRcqsjuLoeOzFZS5DkP8IWJeqEcvgA4MMx3QqvltsCpPh1IUR5Pn3GMbqQo0K3zluHYmuFBneFH5tRlheZ6tOFFYkdNOSokfrUYPUcFSLhoA1JVN5P0DoEHgkZUgDBK21AbyiBHtGTrHCxlIFf1100Jb3svnZ6m750tGhAKpw7l4mrpNZHINlpQWjDTXCJIkoCC4A6Z';

        $this->usersTable->updatePassword($dbId, $invalidPassword);
    }

    /**
     * Test id domain validation when updating password
     */
    public function testUpdatePasswordIdValue()
    {
        $this->expectException(DomainException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidId = -1;
        $password = '$2y$10$Dm9oI/pqrr1706eOPcehLeTnWZ8f0iJVR02WEPsGr8N5muXUrBkRK';

        $this->usersTable->updatePassword($invalidId, $password);
    }

    /**
     * Test id type validation when updating password
     */
    public function testUpdatePasswordIdType()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidId = 'id';
        $password = '$2y$10$Dm9oI/pqrr1706eOPcehLeTnWZ8f0iJVR02WEPsGr8N5muXUrBkRK';

        $this->usersTable->updatePassword($invalidId, $password);
    }

    /**
     * Test id existence when updating password
     */
    public function testUpdatePasswordIdExistence()
    {
        $this->expectException(NotFoundException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidId = 293;
        $password = '$2y$10$Dm9oI/pqrr1706eOPcehLeTnWZ8f0iJVR02WEPsGr8N5muXUrBkRK';

        $this->usersTable->updatePassword($invalidId, $password);
    }

    /**
     * Test deleting a User
     */
    public function testDeleteUser()
    {
        $this->expectException(NotFoundException::class);
        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $dbId = (int) $this->dbTable->getValue(2, 'id');
        $dbUsername = $this->dbTable->getValue(2, 'username');
        $dbEmail = $this->dbTable->getValue(2, 'email');
        $dbPassword = $this->dbTable->getValue(2, 'password');

        $this->usersTable->delete($dbId);

        $user = $this->usersTable->readById($dbId);
    }

    /**
     * Test id value validation when deleting a User
     */
    public function testDeleteUserIdValue()
    {
        $this->expectException(DomainException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidId = -1;

        $this->usersTable->delete($invalidId);
    }

    /**
     * Test id type validation when deleting a User
     */
    public function testDeleteUserIdType()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidId = 'password';

        $this->usersTable->delete($invalidId);
    }

    /**
     * Test id existence when deleting a User
     */
    public function testDeleteUserIdExistence()
    {
        $this->expectException(NotFoundException::class);

        $this->insertDataSet($this->getDataSet('read-username'), 'users');

        $invalidId = 293;

        $this->usersTable->delete($invalidId);
    }

}
