<?php

namespace OopLogin\Model\Entity;

/**
 * Represents a single user in the database.
 */
class Login
{
    /**
     * The id of the user logging in
     * @var mixed
     */
    protected $userId;

    /**
     * The time of the login
     * @var mixed
     */
    protected $time;

    /**
     * The primary id of the login in the database
     * @var mixed
     */
    protected $id;

    /**
     * Constructor
     *
     * @param mixed $userId The user logging in
     * @param mixed $time The time of the login
     * @param mixed $id The id of the login record
     */
    public function __construct($userId = null, $time = null, $id = null)
    {
        $this->userId = $userId;
        $this->time = $time;
        $this->id = $id;
    }

    /**
     * Get the user id of the login record.
     *
     * @return mixed
     */
    public function userId()
    {
        return $this->userId;
    }

    /**
     * Get the time of the login record.
     *
     * @return mixed
     */
    public function time()
    {
        return $this->time;
    }

    /**
     * Get the id of the login record.
     *
     * @return mixed
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * Set the login's user id
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Set the login time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * Set the user's id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
