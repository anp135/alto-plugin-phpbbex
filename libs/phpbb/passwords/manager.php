<?php
/**
*
* This file is cut of part of the phpBB Forum Software package performed by #135
* for implement phpbb hashing algorithms for using inside AltoCMS
*
*/

namespace phpbb\passwords;

class manager
{
    //135
    protected $defaults;

    protected $type_map;
    protected $convert_flag;
    protected $type;

    public function __construct()
    {
        $this->defaults = \Config::Get('plugin.phpbbex.defaults');
        $this->type_map = \Config::Get('plugin.phpbbex.type_map');
        $this->convert_flag = \Config::Get('plugin.phpbbex.convert_flag');
        $this->type = \Config::Get('plugin.phpbbex.type');
    }

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

		$type = ($type === '' || $type === 'pass') ? $this->type : $type;

		if (isset($this->type_map[$type]))
		{
			switch ($type) {
                case '$2a$':
                    $hashing_algorithm = new driver\bcrypt();
                    break;
                case '$2y$':
                    $hashing_algorithm = new driver\bcrypt_2y();
                    break;
                case '$wcf2$':
                    $hashing_algorithm = new driver\bcrypt_wcf2();
                    break;
                case '$H$':
                    $hashing_algorithm = new driver\salted_md5();
                    break;
                case '$P$':
                    $hashing_algorithm = new driver\phpass();
                    break;
                case '$СP$':
                    $hashing_algorithm = new driver\convert_password();
                    break;
                case '$smf$':
                    $hashing_algorithm = new driver\sha1_smf();
                    break;
                case '$md5_phpbb2$':
                    $hashing_algorithm = new driver\md5_phpbb2();
                    break;
                case '$md5_mybb$':
                    $hashing_algorithm = new driver\md5_mybb();
                    break;
                case '$md5_vb$':
                    $hashing_algorithm = new driver\md5_vb();
                    break;
            }
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
	* @param array	$user_row not used
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
			return driver\salted_md5::check($password, $hash);
		}

        //135
        switch($driver) {
            case 'bcrypt':
                $stored_hash_type = new driver\bcrypt();
                break;
            case 'bcrypt_2y':
                $stored_hash_type = new driver\bcrypt_2y();
                break;
            case 'bcrypt_wcf2':
                $stored_hash_type = new driver\bcrypt_wcf2();
                break;
            case 'salted_md5':
                $stored_hash_type = new driver\salted_md5();
                break;
            case 'phpass':
                $stored_hash_type = new driver\phpass();
                break;
            case 'convert_password':
                $stored_hash_type = new driver\convert_password();
                break;
            case 'sha1_smf':
                $stored_hash_type = new driver\sha1_smf();
                break;
            case 'md5_phpbb2':
                $stored_hash_type = new driver\md5_phpbb2();
                break;
            case 'md5_mybb':
                $stored_hash_type = new driver\md5_mybb();
                break;
            case 'md5_vb':
                $stored_hash_type = new driver\md5_vb();
                break;
        }

        if(!$stored_hash_type) return false;

		return $stored_hash_type->check($password, $hash);
	}
}
