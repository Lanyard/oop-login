<?php

namespace OopLogin\Model\Table;

use OopLogin\Exception\DuplicateUsernameException;
use OopLogin\Exception\DuplicateEmailException;
use OopLogin\Exception\NotFoundException;
use OopLogin\Model\Entity\Login;
use OopLogin\Model\Table;
use PDO;
use DomainException;
use InvalidArgumentException;
use LengthException;
use PDOException;

/**
 * Performs PDO operations on Logins.
 */
class LoginsTable extends Table
{
    /**
     * Add a User to the table
     *
     * @param User $user The user to add to the table
     *
     * @return void
     */
    public function create($login)
    {
        $userId = $login->userId();
        $time = $login->time();

        $this->validateId($userId, 'user id');

        $this->validateDatetime($time, 'time');

        try {
            $stmt = $this->connection->prepare('INSERT INTO logins (user_id, time) VALUES (:user_id, :time)');
            $stmt->execute(array(':user_id' => $userId, ':time' => $time));
        } catch (PDOException $e) {
        }
    }

    /**
     * Retrieve an array of Users from the table
     *
     * @return User[]
     */
    public function read()
    {
        $logins = array();
        $stmt = $this->connection->prepare('SELECT * FROM logins');
        if ($stmt->execute()) {
            while ($row = $stmt->fetch()) {
                $login = new Login((int) $row['user_id'], $row['time'], (int) $row['id']);
                $logins[] = $login;
            }
        }
        return $logins;
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
        $stmt = $this->connection->prepare('SELECT * FROM logins WHERE username = ? LIMIT 1');
        if ($stmt->execute(array($username))) {
            $row = $stmt->fetch();
            $user = new User($row['username'], $row['email'], $row['password'], /*(int)*/ $row['id']);
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
        $stmt = $this->connection->prepare('SELECT * FROM logins WHERE email = ? LIMIT 1');
        if ($stmt->execute(array($email))) {
            $row = $stmt->fetch();
            $user = new User($row['username'], $row['email'], $row['password'], /*(int)*/ $row['id']);
            return $user;
        }
        return new User();
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
        $stmt = $this->connection->prepare('SELECT * FROM logins WHERE id = ? LIMIT 1');
        if ($stmt->execute(array($id))) {
            $row = $stmt->fetch();
            $login = new Login((int) $row['user_id'], $row['time'], (int) $row['id']);
            return $login;
        }
        return new Login();
    }

    /**
     * Retrieve a Login from the table by user id
     *
     * @param int $userId the id of the user to retrieve logins of
     *
     * @return Login[]
     */
    public function readByUserId($userId)
    {
        $logins = array();
        $this->validateId($userId, 'user id');
        $stmt = $this->connection->prepare('SELECT * FROM logins WHERE user_id = ?');
        if ($stmt->execute(array($userId))) {
            while ($row = $stmt->fetch()) {
                $logins[] = new Login((int) $row['user_id'], $row['time'], (int) $row['id']);
            }
        }
        return $logins;
    }

    /**
     * Update a User's username
     *
     * @param int $id The id of the user to update
     * @throws OopLogin\Exception\NotFoundException
     *
     * @return void
     */
    public function updateUsername($id, $username)
    {
        $this->validateId($id);
        $this->validateUsername($username);
        $user = $this->readById($id);
        if ($user->id() == null) {
            throw new NotFoundException('No user with the given id was found.');
        }
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
     * @throws OopLogin\Exception\NotFoundException
     *
     * @return void
     */
    public function updateEmail($id, $email)
    {
        $this->validateId($id);
        $this->validateEmail($email);
        $user = $this->readById($id);
        if ($user->id() == null) {
            throw new NotFoundException('No user with the given id was found.');
        }
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
        $this->validateId($id);
        $this->validatePassword($password);
        $user = $this->readById($id);
        if ($user->id() == null) {
            throw new NotFoundException('No user with the given id was found.');
        }
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
        $this->validateId($id);
        $user = $this->readById($id);
        if ($user->id() == null) {
            throw new NotFoundException('No user with the given id was found.');
        }
        $stmt = $this->connection->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(array(':id' => $id));
    }
}
