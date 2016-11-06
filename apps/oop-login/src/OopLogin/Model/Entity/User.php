<?php

namespace OopLogin\Model\Entity;

/**
 * Represents a single user in the database.
 */
class User
{
    /**
    * The primary id of the user in the database
    *
    * @var integer
    */
    protected $id;

    /**
    * The name of the user
    *
    * @var string
    */
    protected $username;

    /**
    * The user's email address
    *
    * @var string
    */
    protected $email;

    /**
    * The user's password
    *
    * @var string
    */
    protected $password;

    /**
    * Constructor
    *
    * @param integer $id The id of the user record.
    * @param string $username The name of the user
    * @param string $email The user's email address
    * @param string $password the user's password
    */
    public function __construct($id = -1, $username = '', $email = '', $password = '')
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
    }

    /**
    * Get the id of the user record.
    */
    public function id()
    {
        return $this->id;
    }

    /**
    * Get the user's name.
    */
    public function username()
    {
        return $this->username;
    }

    /**
    * Get the user's email address.
    */
    public function email()
    {
        return $this->email;
    }

    /**
    * Get the user's password.
    */
    public function password()
    {
        return $this->password;
    }
}
