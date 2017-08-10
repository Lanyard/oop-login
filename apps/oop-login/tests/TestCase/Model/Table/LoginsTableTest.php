<?php

namespace OopLogin\Test\TestCase\Table;

use OopLogin\Exception\DuplicateUsernameException;
use OopLogin\Exception\DuplicateEmailException;
use OopLogin\Exception\NotFoundException;
use OopLogin\Model\Entity\Login;
use OopLogin\Model\Table\LoginsTable;
use PDO;
use PHPUnit\Framework\TestCase;
use PHPUnit_Extensions_Database_DataSet_DataSetFilter;
use PHPUnit_Extensions_Database_TestCase;
use DomainException;
use InvalidArgumentException;
use LengthException;

class LoginsTableTest extends PHPUnit_Extensions_Database_TestCase
{
    static private $pdo = null;
    private $connection = null;
    private $loginsTable;
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
        } elseif ($set == 'read-login') {
            return $this->createMySQLXMLDataSet('tests/Fixture/oop-login_read-login.xml');
        } else {
            return;
        }
    }

    public function insertDataSet($dataSet, $tableName)
    {
        $this->dbTable = $dataSet->getTable($tableName);

        $rowCount = $this->dbTable->getRowCount();

        $stmt = self::$pdo->prepare('INSERT INTO logins (user_id, time) VALUES (:user_id, :time)');

        for ($i = 0; $i < $rowCount; $i++) {
            $row = $this->dbTable->getRow($i);
            $stmt->execute(array(':user_id' => $row['user_id'], ':time' => $row['time']));
        }
    }

    /**
     * setUp method.
     */
    public function setUp()
    {
        $this->getConnection();
        $tableName = 'logins';

        $this->loginsTable = new loginsTable(self::$pdo);

        $dataSet = $this->getDataSet();
        
        $this->insertDataSet($dataSet, $tableName);
    }

    /**
     * tearDown method.
     */
    public function tearDown()
    {
        $stmt = self::$pdo->prepare('TRUNCATE logins');
        $stmt->execute();
    }

    /**
     * Test connection getter
     */
    public function testConnection()
    {
        $this->assertEquals(self::$pdo, $this->loginsTable->connection());
    }

    /**
     * Test connection type
     */
    public function testConnectionType()
    {
        $this->expectException(InvalidArgumentException::class);
        $mysqliConnection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);
        $invalidTable = new loginsTable($mysqliConnection);
    }

    /**
     * Test reading one login
     */
    public function testReadLogin()
    {
        $logins = $this->loginsTable->read();
        $login = $logins[0];
        $id = $login->id();
        $userId = $login->userId();
        $time = $login->time();

        $dbId = (int) $this->dbTable->getValue(0, 'id');
        $dbUserId = (int) $this->dbTable->getValue(0, 'user_id');
        $dbTime = $this->dbTable->getValue(0, 'time');

        $this->assertEquals($dbId, $id);
        $this->assertEquals($dbUserId, $userId);
        $this->assertEquals($dbTime, $time);
    }

    /**
     * Validate returned id type when reading login
     */
    public function testReadLoginIdType()
    {
        $logins = $this->loginsTable->read();
        $login = $logins[0];
        $id = $login->id();

        $this->assertEquals(true, is_int($id));
    }

    /**
     * Test creating one login
     */
    public function testCreateLogin()
    {
        $newUserId = 8;
        $newTime = '2017-10-28 07:43:08';
        $newLogin = new Login($newUserId, $newTime);
        $this->loginsTable->create($newLogin);

        $fullDataSet = $this->getConnection()->createDataSet(['logins']);
        $dataSet = new PHPUnit_Extensions_Database_DataSet_DataSetFilter($fullDataSet);
        $dataSet->addIncludeTables(['logins']);
        $dataSet->setIncludeColumnsForTable('logins', ['id', 'user_id', 'time']);
        $expectedDataSet = $this->createMySQLXMLDataSet('tests/Fixture/oop-login_create-login.xml');
        
        $this->assertDataSetsEqual($expectedDataSet, $dataSet);
    }

    /**
     * Test user id type when creating login
     */
    public function testCreateLoginUserIdType()
    {
        $this->expectException(InvalidArgumentException::class);

        $invalidUserId = 'sorry';
        $newTime = '2015-03-12 04:32:14';
        $newLogin = new Login($invalidUserId, $newTime);

        $this->loginsTable->create($newLogin);
    }

    /**
     * Test user id value when creating login
     */
    public function testCreateLoginUserIdValue()
    {
        $this->expectException(DomainException::class);
        
        $invalidUserId = -1;
        $newTime = '2015-03-12 04:32:14';
        $invalidLogin = new Login($invalidUserId, $newTime);

        $this->loginsTable->create($invalidLogin);
    }

    /**
     * Test time type when creating login
     */
    public function testCreateLoginTimeType()
    {
        $this->expectException(InvalidArgumentException::class);
        
        $newUserId = 3;
        $invalidTime = 'oaviom';
        $invalidLogin = new Login($newUserId, $invalidTime);
        
        $this->loginsTable->create($invalidLogin);
    }
}
