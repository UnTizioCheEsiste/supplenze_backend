<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitd6731f38689e318bf2683e31630852b4
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
        'F' => 
        array (
            'Francesco\\Supplenze\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
        'Francesco\\Supplenze\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitd6731f38689e318bf2683e31630852b4::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitd6731f38689e318bf2683e31630852b4::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitd6731f38689e318bf2683e31630852b4::$classMap;

        }, null, ClassLoader::class);
    }
}
