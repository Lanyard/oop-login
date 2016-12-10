<?php

namespace OopLogin\Model\Entity;

use InvalidArgumentException;

/**
 * Represents a single user in the database.
 */
class User
{
    /**
    * The primary id of the user in the database
    */
    protected $id;

    /**
    * The name of the user
    */
    protected $username;

    /**
    * The user's email address
    */
    protected $email;

    /**
    * The user's hashed password
    */
    protected $password;

    /**
    * Constructor
    *
    * @param $id The id of the user record
    * @param $username The name of the user
    * @param $email The user's email address
    * @param $password the user's password
    */
    public function __construct($id = '', $username = '', $email = '', $password = '')
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
    }

    /**
    * Get the id of the user record.
    *
    * @return int
    */
    public function id()
    {
        return $this->id;
    }

    /**
    * Get the user's name.
    *
    * @return string
    */
    public function username()
    {
        return $this->username;
    }

    /**
    * Get the user's email address.
    *
    * @return string
    */
    public function email()
    {
        return $this->email;
    }

    /**
    * Get the user's password.
    *
    * @return string
    */
    public function password()
    {
        return $this->password;
    }
}
