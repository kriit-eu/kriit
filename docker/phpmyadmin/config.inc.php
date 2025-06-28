<?php
/**
 * phpMyAdmin configuration file
 */

$cfg['blowfish_secret'] = 'kriit-secret-key-for-phpmyadmin-cookies';

$i = 0;

$i++;
$cfg['Servers'][$i]['auth_type'] = 'config';
$cfg['Servers'][$i]['host'] = 'db';  // Use Docker service name, not 127.0.0.1
$cfg['Servers'][$i]['port'] = 8002;
$cfg['Servers'][$i]['compress'] = false;
$cfg['Servers'][$i]['AllowNoPassword'] = true;
$cfg['Servers'][$i]['user'] = 'root';
$cfg['Servers'][$i]['password'] = 'kriitkriit';

$cfg['UploadDir'] = '';
$cfg['SaveDir'] = '';
$cfg['TempDir'] = '/tmp';