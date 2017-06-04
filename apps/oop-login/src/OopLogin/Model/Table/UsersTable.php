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
            throw new InvalidArgumentException('The ' . $name . ' is not a string.');
        }
        if (empty($field)) {
            throw new DomainException('The ' . $name . ' is empty.');
        }
        if (strlen($field) > 255) {
            throw new LengthException('The ' . $name . ' is too long.');
        }
    }

    /**
     * Validate a User field for an int column in the table
     *
     * @param int $field The field-value to validate
     * @param string $name The name of the field for Exception messages
     *
     * @throws DomainException
     * @throws InvalidArgumentException
     * @throws LengthException
     *
     * @return void
     */
    protected function validateInt($field, $name)
    {
        if (!is_int($field)) {
            throw new InvalidArgumentException('The ' . $name . ' is not an integer.');
        }
        if (($field < 0) || ($field > 4294967295)) {
            throw new DomainException('The ' . $name . ' is outside the valid numerical range.');
        }
    }

    /**
     * Validate User id
     *
     * @param int $id the User's id
     *
     * @return void
     */
    protected function validateId($id)
    {
        $this->validateInt($id, 'id');
    }

    /**
     * Validate all needed User fields for the table
     *
     * @param string $username the User's username
     *
     * @return void
     */
    protected function validateUsername($username)
    {
        $this->validateVarchar255($username, 'username');
    }

    /**
     * Validate all needed User fields for the table
     *
     * @param string $email the User's email
     *
     * @return void
     */
    protected function validateEmail($email)
    {
        $this->validateVarchar255($email, 'email');
    }

    /**
     * Validate all needed User fields for the table
     *
     * @param string $password the User's password
     *
     * @return void
     */
    protected function validatePassword($password)
    {
        $this->validateVarchar255($password, 'password');
    }

    /**
     * Check for a duplicate username exception
     *
     * @param array $e the exception to handle
     *
     * @throws OopLogin\Exception\DuplicateUsernameException
     *
     * @return void
     */
    protected function checkDuplicateUsername($e)
    {
        if (strstr($e->errorInfo[2], 'key \'username\'')) {
            throw new DuplicateUsernameException('The new username is already in the database');
        }
    }

    /**
     * Check for a duplicate email exception
     *
     * @param array $e the exception to handle
     *
     * @throws OopLogin\Exception\DuplicateEmailException
     *
     * @return void
     */
    protected function checkDuplicateEmail($e)
    {
        if (strstr($e->errorInfo[2], 'key \'email\'')) {
            throw new DuplicateEmailException('The new email is already in the database');
        }
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
     * Add a User to the table
     *
     * @param User $user The user to add to the table
     *
     * @return void
     */
    public function create($user)
    {
        $username = $user->username();
        $email = $user->email();
        $password = $user->password();
        $this->validateUsername($username);
        $this->validateEmail($email);
        $this->validatePassword($password);
        try {
            $stmt = $this->connection->prepare('INSERT INTO users (username, email, password) VALUES (:username, :email, :password)');
            $stmt->execute(array(':username' => $username, ':email' => $email, ':password' => $password));
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $this->checkDuplicateUsername($e);
                $this->checkDuplicateEmail($e);
            }
        }
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
                $user = new User($row['username'], $row['email'], $row['password'], (int) $row['id']);
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
        $this->validateUsername($username);
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        if ($stmt->execute(array($username))) {
            $row = $stmt->fetch();
            $user = new User($row['username'], $row['email'], $row['password'], (int) $row['id']);
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
        $this->validateEmail($email);
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        if ($stmt->execute(array($email))) {
            $row = $stmt->fetch();
            $user = new User($row['username'], $row['email'], $row['password'], (int) $row['id']);
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
        $this->validateId($id, 'id');
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        if ($stmt->execute(array($id))) {
            $row = $stmt->fetch();
            $user = new User($row['username'], $row['email'], $row['password'], (int) $row['id']);
            return $user;
        }
        return new User();
    }

    /**
     * Update a User's username
     *
     * @param String $id The id of the user to update
     * @throws LengthException
     *
     * @return void
     */
    public function updateUsername($id, $username)
    {
        $this->validateId($id);
        $this->validateUsername($username);
        try {
            $stmt = $this->connection->prepare('UPDATE users SET username = :username WHERE id = :id');
            $stmt->execute(array(':username' => $username, ':id' => $id));
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $this->checkDuplicateUsername($e);
            }
        }
    }

    /**
     * Update a User's email
     *
     * @param String $id The id of the user to update
     * @throws LengthException
     *
     * @return void
     */
    public function updateEmail($id, $email)
    {
        $this->validateId($id);
        $this->validateEmail($email);
        try {
            $stmt = $this->connection->prepare('UPDATE users SET EMAIL = :email WHERE id = :id');
            $stmt->execute(array(':email' => $email, ':id' => $id));
        } catch (PDOException $e) {
            $this->checkDuplicateEmail($e);
        }
    }
}
