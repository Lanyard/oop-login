<?php

namespace OopLogin\Model\Table;

use OopLogin\Exception\DuplicateUsernameException;
use OopLogin\Exception\DuplicateEmailException;
use OopLogin\Exception\NotFoundException;
use OopLogin\Model\Entity\User;
use OopLogin\Model\Table;
use PDO;
use DomainException;
use InvalidArgumentException;
use LengthException;
use PDOException;

/**
 * Performs PDO operations on Users.
 */
class UsersTable extends Table
{
    /**
     * Validate username for the table
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
     * Validate email for the table
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
     * Validate password for the table
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
            if ($row) {
                $user = new User($row['username'], $row['email'], $row['password'], (int) $row['id']);
                return $user;
            }
        }
        return null;
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
            if ($row) {
                $user = new User($row['username'], $row['email'], $row['password'], (int) $row['id']);
                return $user;
            }
        }
        return null;
    }

    /**
     * Retrieve a User from the table by id
     *
     * @param int $id The id of the user to retrieve
     *
     * @return User
     */
    public function readById($id)
    {
        $this->validateId($id, 'id');
        $stmt = $this->connection->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        if ($stmt->execute(array($id))) {
            $row = $stmt->fetch();
            if ($row) {
                $user = new User($row['username'], $row['email'], $row['password'], (int) $row['id']);
                return $user;
            }
        }
        return null;
    }

    /**
     * Update a User's username
     *
     * @param int $id The id of the user to update
     *
     * @return void
     */
    public function updateUsername($id, $username)
    {
        $this->validateUsername($username);
        $user = $this->readById($id);
        $this->validateEntity($user, 'user');
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
     * @param int $id The id of the user to update
     *
     * @return void
     */
    public function updateEmail($id, $email)
    {
        $this->validateEmail($email);
        $user = $this->readById($id);
        $this->validateEntity($user, 'user', 'email');
        try {
            $stmt = $this->connection->prepare('UPDATE users SET email = :email WHERE id = :id');
            $stmt->execute(array(':email' => $email, ':id' => $id));
        } catch (PDOException $e) {
            $this->checkDuplicateEmail($e);
        }
    }

    /**
     * Update a User's password
     *
     * @param int $id The id of the User to update
     *
     * @return void
     */
    public function updatePassword($id, $password)
    {
        $this->validatePassword($password);
        $user = $this->readById($id);
        $this->validateEntity($user, 'user', 'password');
        $stmt = $this->connection->prepare('UPDATE users SET password = :password WHERE id = :id');
        $stmt->execute(array(':password' => $password, ':id' => $id));
    }

    /**
     * Delete a User
     *
     * @param int $id The id of the User to delete
     *
     * @return void
     */
    public function delete($id)
    {
        $user = $this->readById($id);
        $this->validateEntity($user, 'user', 'password');
        $stmt = $this->connection->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(array(':id' => $id));
    }

}
