<?php

$config = array();

$config['phpbb']['dbname'] = 'phpbb';
$config['phpbb']['table_prefix'] = 'phpbb_';
$config['cookie']['user_id'] = 'phpbb3_u';
$config['cookie']['phpbb_prefix'] = 'phpbb3_';
$config['defaults'] = array(
    0 => 'passwords.driver.bcrypt_2y',
    1 => 'passwords.driver.bcrypt',
    2 => 'passwords.driver.salted_md5',
    3 => 'passwords.driver.phpass',
);
$config['type_map'] = array (
    '$2a$'  =>  'bcrypt',
    '$2y$'  =>  'bcrypt_2y',
    '$wcf2$'    =>  'bcrypt_wcf2',
    '$H$'   =>  'salted_md5',
    '$P$'   =>  'phpass',
    '$CP$'  =>  'convert_password',
    '$smf$' =>  'sha1_smf',
    '$wcf1$'    =>  'sha1_wcf1',
    '$sha1$'    =>  'sha1',
    '$md5_phpbb2$'  =>  'md5_phpbb2',
    '$md5_mybb$'    =>  'md5_mybb',
    '$md5_vb$'  =>  'md5_vb'
);
$config['convert_flag'] = false;
$config['type'] = '$2y$';

return $config;