<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\passwords;

class manager
{
    //135
    protected $defaults = array (
        0 => 'passwords.driver.bcrypt_2y',
        1 => 'passwords.driver.bcrypt',
        2 => 'passwords.driver.salted_md5',
        3 => 'passwords.driver.phpass',
    );

    protected $type_map = array (
        '$2a$'  =>  'bcrypt',
        '$2y$'  =>  'bcrypt_2y',
        '$wcf2$'    =>  'bcrypt_wcf2',
        '$H$'   =>  'salted_md5',
        '$P$'   =>  'phpass',
        '$CP$'  =>  'convert_password'
    );
    protected $convert_flag = false;

	/**
	* Get the algorithm specified by a specific prefix
	*
	* @param string $prefix Password hash prefix
	*
	* @return object|bool The hash type object or false if prefix is not
	*			supported
	*/
	protected function get_algorithm($prefix)
	{
		if (isset($this->type_map[$prefix]))
		{
			return $this->type_map[$prefix];
		}
		else
		{
			return false;
		}
	}

	/**
	* Detect the hash type of the supplied hash
	*
	* @param string $hash Password hash that should be checked
	*
	* @return object|bool The hash type object or false if the specified
	*			type is not supported
	*/
	public function detect_algorithm($hash)
	{
		/*
		* preg_match() will also show hashing algos like $2a\H$, which
		* is a combination of bcrypt and phpass. Legacy algorithms
		* like md5 will not be matched by this and need to be treated
		* differently.
		*/
		if (!preg_match('#^\$([a-zA-Z0-9\\\]*?)\$#', $hash, $match))
		{
			return false;
		}

		// Be on the lookout for multiple hashing algorithms
		// 2 is correct: H\2a > 2, H\P > 2
		if (strlen($match[1]) > 2)
		{
			//135. unsupport twice hashing
		    return false;
		}

		// get_algorithm() will automatically return false if prefix
		// is not supported
		return $this->get_algorithm($match[0]);
	}

	/**
	* Hash supplied password
	*
	* @param string $password Password that should be hashed
	* @param string $type Hash type. Will default to standard hash type if
	*			none is supplied
	* @return string|bool Password hash of supplied password or false if
	*			if something went wrong during hashing
	*/
	public function hash($password, $type = '')
	{
		if (strlen($password) > 4096)
		{
			// If the password is too huge, we will simply reject it
			// and not let the server try to hash it.
			return false;
		}

		// Try to retrieve algorithm by service name if type doesn't
		// start with dollar sign
		if (!is_array($type) && strpos($type, '$') !== 0 && isset($this->algorithms[$type]))
		{
			$type = $this->algorithms[$type]->get_prefix();
		}

		$type = ($type === '') ? $this->type : $type;

		if (is_array($type))
		{
			return $this->combined_hash_password($password, $type);
		}

		if (isset($this->type_map[$type]))
		{
			$hashing_algorithm = $this->type_map[$type];
		}
		else
		{
			return false;
		}

		return $hashing_algorithm->hash($password);
	}

	/**
	* Check supplied password against hash and set convert_flag if password
	* needs to be converted to different format (preferrably newer one)
	*
	* @param string $password Password that should be checked
	* @param string $hash Stored hash
	* @param array	$user_row User's row in users table
	* @return string|bool True if password is correct, false if not
	*/
	public function check($password, $hash, $user_row = array())
	{
		if (strlen($password) > 4096)
		{
			// If the password is too huge, we will simply reject it
			// and not let the server try to hash it.
			return false;
		}

		// Empty hashes can't be checked
		if (empty($hash))
		{
			return false;
		}

		// First find out what kind of hash we're dealing with
        //135
		$driver = $this->detect_algorithm($hash);
		if ($driver == false)
		{
			// Still check MD5 hashes as that is what the installer
			// will default to for the admin user
			return $this->get_algorithm('$H$')->check($password, $hash);
		}

        //135
        switch($driver) {
            case 'bcrypt':
                $stored_hash_type = new driver\bcrypt();
                break;
            case 'bcrypt_2y':
                $stored_hash_type = new driver\bcrypt_2y();
                break;
        }

		return $stored_hash_type->check($password, $hash);
	}
}
