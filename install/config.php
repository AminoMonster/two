<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

return array(
    // Add-on names to be installed
    // If empty will be installed only addons included by default
    'addons' => array(),

    'cart_settings' => array (
        'email' => 'admin@example.com',
        'password' => 'admin',
        'secret_key' => 'YOURVERYSECRETCEY',
        'languages' => array (
            'en', 'ru'
        ),
        'main_language' => 'en',
        'demo_catalog' => true,
        'theme_name' => 'basic',
        'license_number' => ''
    ),
    'database_settings' => array(
        'host' => 'localhost',
        'name' => 'installer_test',
        'user' => 'root',
        'password' => 'password',
        'table_prefix' => 'cscart_',
        'database_backend' => 'mysqli',
        'notify' => false,
    ),
    'server_settings' => array (
        'http_host' => 'localhost',
        'http_path' => '',
        'https_host' => '',
        'https_path' => '',
        'correct_permissions' => true,
    ),
);
