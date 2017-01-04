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
     * Add a User to the table
     *
     * @param User $user The user to add to the table
     *
     * @throws OopLogin\Exception\DuplicateUsernameException
     * @throws OopLogin\Exception\DuplicateEmailException
     * @throws DomainException
     * @throws InvalidArgumentException
     * @throws LengthException
     *
     * @return void
     */
    public function create($user)
    {
        $username = $user->username();
        if (!is_string($username)) {
            throw new InvalidArgumentException('The username is not a string');
        }
        if (empty($username)) {
            throw new DomainException('The user has no username');
        }
        if (strlen($username) > 255) {
            throw new LengthException('The username is too long');
        }
        try {
            $stmt = $this->connection->prepare('INSERT INTO users (username, email, password) VALUES (:username, :email, :password)');
            $stmt->execute(array(':username' => $user->username(), ':email' => $user->email(), ':password' => $user->password()));
        }
        catch(PDOException $e) {
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
