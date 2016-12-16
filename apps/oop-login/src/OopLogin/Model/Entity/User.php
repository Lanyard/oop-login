<?php

namespace OopLogin\Model\Entity;

/**
 * Represents a single user in the database.
 */
class User
{
    /**
     * The name of the user
     * @var mixed
     */
    protected $username;

    /**
     * The user's email address
     * @var mixed
     */
    protected $email;

    /**
     * The user's hashed password
     * @var mixed
     */
    protected $password;

    /**
     * The primary id of the user in the database
     * @var mixed
     */
    protected $id;

    /**
     * Constructor
     *
     * @param mixed $id The id of the user record
     * @param mixed $username The name of the user
     * @param mixed $email The user's email address
     * @param mixed $password the user's password
     */
    public function __construct($username = '', $email = '', $password = '', $id = '')
    {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->id = $id;
    }

    /**
     * Get the user's name.
     *
     * @return mixed
     */
    public function username()
    {
        return $this->username;
    }

    /**
     * Get the user's email address.
     *
     * @return mixed
     */
    public function email()
    {
        return $this->email;
    }

    /**
     * Get the user's password.
     *
     * @return mixed
     */
    public function password()
    {
        return $this->password;
    }

    /**
     * Get the id of the user record.
     *
     * @return mixed
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * Set the user's username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Set the user's email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Set the user's password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Set the user's id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
