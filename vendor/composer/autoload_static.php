<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6c5b84a72b92b70246d41b16439c5875
{
    public static $prefixLengthsPsr4 = array (
        'Y' => 
        array (
            'YandexStation\\' => 14,
        ),
        'S' => 
        array (
            'ServerYaMetrika\\' => 16,
        ),
        'P' => 
        array (
            'PBurggraf\\CRC\\' => 14,
        ),
        'C' => 
        array (
            'ChatbaseAPI\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'YandexStation\\' => 
        array (
            0 => __DIR__ . '/..' . '/avdeevsv91/yandex-dialogs-php/src',
        ),
        'ServerYaMetrika\\' => 
        array (
            0 => __DIR__ . '/..' . '/avdeevsv91/server_yametrika/src',
        ),
        'PBurggraf\\CRC\\' => 
        array (
            0 => __DIR__ . '/..' . '/pburggraf/crc/src/PBurggraf/CRC',
        ),
        'ChatbaseAPI\\' => 
        array (
            0 => __DIR__ . '/..' . '/bhavyanshu/chatbase-php/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'p' => 
        array (
            'phpMorphy' => 
            array (
                0 => __DIR__ . '/..' . '/umisoft/phpmorphy/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6c5b84a72b92b70246d41b16439c5875::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6c5b84a72b92b70246d41b16439c5875::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit6c5b84a72b92b70246d41b16439c5875::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}