<?php

namespace OopLogin\Model\Table;

use OopLogin\Model\Entity\User;
use PDO;
use InvalidArgumentException;

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
     */
    public function create($user)
    {
        $stmt = $this->connection->prepare('INSERT INTO users (username, email, password) VALUES (:username, :email, :password)');
        $stmt->execute(array(':username' => $user->username(), ':email' => $user->email(), ':password' => $user->password()));
    }
}
