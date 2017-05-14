<?php

namespace OopLogin\Model\Table;

use OopLogin\Exception\DuplicateUsernameException;
use OopLogin\Exception\DuplicateEmailException;
use OopLogin\Model\Entity\User;
use PDO;
use DomainException;
use InvalidArgumentException;
use LengthException;
use PDOException;

/**
 * Performs PDO operations on Users.
 */
class UsersTable
{
    /**
     * The table's PDO connection to the database
     * @var PDO
     */
    protected $connection;

    /**
     * Constructor
     *
     * @param PDO $connection The PDO connection to the database
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    public function __construct($connection)
    {
        if (!($connection instanceof PDO)) {
            throw new InvalidArgumentException('The database connection is not a PDO connection.');
        }

        $this->connection = $connection;
    }

    /**
     * Validate a User field for a varchar(255) column in the table
     *
     * @param string $field The field-value to validate
     * @param string $name The name of the field for Exception messages
     *
     * @throws DomainException
     * @throws InvalidArgumentException
     * @throws LengthException
     *
     * @return void
     */
    protected function validateVarchar255($field, $name)
    {
        if (!is_string($field)) {
            throw new InvalidArgumentException('The ' . $name . ' is not a string');
        }
        if (empty($field)) {
            throw new DomainException('The user has no ' . $name);
        }
        if (strlen($field) > 255) {
            throw new LengthException('The ' . $name . ' is too long');
        }
    }

    /**
     * Validate all needed User fields for the table
     *
     * @param string $username the User's username
     * @param string $email the User's email
     * @param string $password the User's password
     *
     * @return void
     */
    protected function validateUser($username, $email, $password)
    {
        $this->validateVarchar255($username, 'username');
        $this->validateVarchar255($email, 'email');
        $this->validateVarchar255($password, 'password');
    }

    /**
     * Get the PDO connection
     *
     * @return PDO
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * Retrieve an array of Users from the table
     *
     * @return User[]
     */
    public function read()
    {
        $users = array();
        $stmt = $this->connection->prepare('SELECT * FROM users');
        if ($stmt->execute()) {
            while ($row = $stmt->fetch()) {
                $user = new User($row['username'], $row['email'], $row['password'], $row['id']);
                $users[] = $user;
            }
        }
        return $users;
    }

    /**
     * Retrieve a User from the table by username
     *
     * @param String $username The username of the user to retrieve
     *
     * @return User
     */
    public function readByUsername($username)
    {
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        if ($stmt->execute(array($username))) {
            $row = $stmt->fetch();
            $user = new User($row['username'], $row['email'], $row['password'], $row['id']);
            return $user;
        }
        return new User();
    }

    /**
     * Retrieve a User from the table by email
     *
     * @param String $email The email of the user to retrieve
     *
     * @return User
     */
    public function readByEmail($email)
    {
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        if ($stmt->execute(array($email))) {
            $row = $stmt->fetch();
            $user = new User($row['username'], $row['email'], $row['password'], $row['id']);
            return $user;
        }
        return new User();
    }

    /**
     * Retrieve a User from the table by id
     *
     * @param String $id The id of the user to retrieve
     *
     * @return User
     */
    public function readById($id)
    {
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        if ($stmt->execute(array($id))) {
            $row = $stmt->fetch();
            $user = new User($row['username'], $row['email'], $row['password'], $row['id']);
            return $user;
        }
        return new User();
    }

    /**
     * Add a User to the table
     *
     * @param User $user The user to add to the table
     *
     * @throws OopLogin\Exception\DuplicateUsernameException
     * @throws OopLogin\Exception\DuplicateEmailException
     *
     * @return void
     */
    public function create($user)
    {
        $username = $user->username();
        $email = $user->email();
        $password = $user->password();
        $this->validateUser($username, $email, $password);
        try {
            $stmt = $this->connection->prepare('INSERT INTO users (username, email, password) VALUES (:username, :email, :password)');
            $stmt->execute(array(':username' => $username, ':email' => $email, ':password' => $password));
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                if (strstr($e->errorInfo[2], 'key \'username\'')) {
                    throw new DuplicateUsernameException('The new username is already in the database');
                }
                if (strstr($e->errorInfo[2], 'key \'email\'')) {
                    throw new DuplicateEmailException('The new email is already in the database');
                }
            }
        }
    }
}
