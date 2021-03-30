<?php


namespace App\Services;


class BaseService
{
    // 三个私有 一个公有 两个静态
    protected static $instance;

    // self 和 static 区别
    // static 指的是当前类(子类使用self 代表子类， 父类使用代表父类)
    // self (self代码写在哪里，就指哪个类)
    public static function getInstance()
    {
        if (static::$instance instanceof static) {
            return static::$instance;
        }
        static::$instance = new static();
        return static::$instance;
    }
//    public static function getInstance()
//    {
//        if (self::$instance instanceof self) {
//            return self::$instance;
//        }
//        self::$instance = new self();
//    }

    private function __construct()
    {

    }

    private function __clone()
    {

    }
}
