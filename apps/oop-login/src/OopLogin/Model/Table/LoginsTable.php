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
     * Validate User id field for the table
     *
     * @param int $id The User id to validate
     *
     * @return void
     */
    protected function validateUserId($id)
    {
        $this->validateId($id, 'user id');
    }
    /**
     * Add a Login to the table
     *
     * @param Login $login The Login to add to the table
     *
     * @return void
     */
    public function create($login)
    {
        $userId = $login->userId();
        $time = $login->time();

        $this->validateUserId($userId);

        $this->validateDatetime($time, 'time');

        try {
            $stmt = $this->connection->prepare('INSERT INTO logins (user_id, time) VALUES (:user_id, :time)');
            $stmt->execute(array(':user_id' => $userId, ':time' => $time));
        } catch (PDOException $e) {
        }
    }

    /**
     * Retrieve an array of Logins from the table
     *
     * @return Login[]
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
     * Retrieve a Login from the table by id
     *
     * @param int $id The id of the Login to retrieve
     *
     * @return Login
     */
    public function readById($id)
    {
        $this->validateId($id);
        $stmt = $this->connection->prepare('SELECT * FROM logins WHERE id = ? LIMIT 1');
        if ($stmt->execute(array($id))) {
            $row = $stmt->fetch();
            if ($row) {
                $login = new Login((int) $row['user_id'], $row['time'], (int) $row['id']);
                return $login;
            }
        }
        return null;
    }

    /**
     * Retrieve Logins from the table by User id
     *
     * @param int $userId the id of the User to retrieve Logins of
     *
     * @return Login[]
     */
    public function readByUserId($userId)
    {
        $logins = array();
        $this->validateUserId($userId);
        $stmt = $this->connection->prepare('SELECT * FROM logins WHERE user_id = ?');
        if ($stmt->execute(array($userId))) {
            while ($row = $stmt->fetch()) {
                $logins[] = new Login((int) $row['user_id'], $row['time'], (int) $row['id']);
            }
        }
        return $logins;
    }

    /**
     * Delete a Login
     *
     * @param int $id The id of the Login to delete
     *
     * @throws OopLogin/Exception/NotFoundException
     *
     * @return void
     */
    public function delete($id)
    {
        $login = $this->readById($id);
        $this->validateEntity($login, 'login', 'id');
        $stmt = $this->connection->prepare('DELETE FROM logins WHERE id = :id');
        $stmt->execute(array(':id' => $id));
    }

}
