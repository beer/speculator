<?php
define("WWW_HOST", $_SERVER['SERVER_NAME']);
define("STATIC_PATH", '');
//define('MEMCACHEHOST', 'localhost');
//define('MEMCACHEPORT', '11211');

// session_handler
//ini_set('session.save_handler', 'memcache');
//ini_set('session.save_path', 'tcp://' . MEMCACHEHOST . ':' . MEMCACHEPORT);
//ini_set('session.name', 'SIMSESSID');
//ini_set('session.gc_maxlifetime', 60*60*24);

// add Memcache Server
//Pix_Cache::addServer('Pix_Cache_Adapter_Memcache', json_decode(file_get_contents('/services/config/memcache.json'), true));

require (__DIR__ . '/extlibs/Symfony/Component/ClassLoader/UniversalClassLoader.php');

use Symfony\Component\ClassLoader\UniversalClassLoader;

call_user_func(function() {
    $loader = new UniversalClassLoader();
    $loader->register();
    $loader->registerNamespaceFallback(EATGOCORE_PATH . '/extlibs');
    $loader->registerPrefixFallbacks(array(
        EATGOCORE_PATH . '/libs',
        EATGOCORE_PATH . '/extlibs',
        EATGOCORE_PATH . '/models',
        EATGOCORE_PATH . '/forms',
        __DIR__ . '/models',
        __DIR__ . '/helpers',
        __DIR__ . '/forms',
        __DIR__ . '/libs'
    ));
});

// show SQL query
Pix_Table::enableLog(Pix_Table::LOG_QUERY);
