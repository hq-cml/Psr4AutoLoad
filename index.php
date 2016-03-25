<?php
/**
 * Created by IntelliJ IDEA.
 * User: didi
 * Date: 16/3/25
 * Time: 下午6:26
 */

class App{
    protected static $apppath = null;
    private static function init(){
        self::$apppath = realpath(dirname("./"));
        $base_dir = self::$apppath.DIRECTORY_SEPARATOR."vendor".DIRECTORY_SEPARATOR;
        include_once("./auto/Psr4AutoLoader.php");
        $Autoload = new auto\Psr4AutoLoader();
        $Autoload->addNamespace("vendor", $base_dir);
        $Autoload->register();
    }

    public static function run(){
        self::init();
        $c = new vendor\vendor1\MyClass1();
        new vendor\vendor2\MyClass2();

        $c->hello();
    }
}

App::run();