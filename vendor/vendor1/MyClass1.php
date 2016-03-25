<?php

namespace vendor\vendor1;

/**
 * Created by IntelliJ IDEA.
 * User: didi
 * Date: 16/3/25
 * Time: 下午6:22
 */
class MyClass1 {
    public function __construct()
    {
        echo "[vendor/vendor1/MyClass1.php] is loaded\n";
    }

    public function hello(){
        echo "hello, world!!\n";
    }
}