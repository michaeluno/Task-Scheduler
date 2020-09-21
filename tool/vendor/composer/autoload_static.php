<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1bd65f3959f22299bb82bc8bb56c80fc
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPClassMapGenerator\\' => 21,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPClassMapGenerator\\' => 
        array (
            0 => __DIR__ . '/..' . '/michaeluno/php-classmap-generator/source',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1bd65f3959f22299bb82bc8bb56c80fc::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1bd65f3959f22299bb82bc8bb56c80fc::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
