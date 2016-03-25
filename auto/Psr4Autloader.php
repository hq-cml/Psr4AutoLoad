<?php
namespace auto;

class Psr4AutoLoader {
  /**
   * 命名空间前缀和具体路径对应的映射表(这在composer中有相同的东西)
   * 一个命名空间前缀中可以有多个路径
   */
   protected $prefixes = array();

   /**
    * 注册加载函数到自动加载函数栈中
    *
    * @return void
    */
   public function register() {
       spl_autoload_register(array($this, 'loadClass'));
   }

   /**
 * 给一个命名空间前缀中添加具体的路径.
 *
 * @param string $prefix 命名空间前缀
 * @param string $base_dir 要添加到命名空间中的路径
 * @param bool $prepend 如果为true，则将该路径添加到命名空间对应数组的
 *             最前面，否则,添加到最后面.(这个会影响自动加载的搜索文件)
 *
 * @return void
 */
public function addNamespace($prefix, $base_dir, $prepend = false)
{
    // 正规化命名空间前缀
    $prefix = trim($prefix, '\\') . '\\';

    // 正规化命名空间对应的目录
    $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . '/';

    // 初始化命名空间中该前缀的数组
    if (isset($this->prefixes[$prefix]) === false) {
        $this->prefixes[$prefix] = array();
    }

    // 将目录添加到命名空间数组中$prefix前缀数组中
    if ($prepend) {
        array_unshift($this->prefixes[$prefix], $base_dir);
    } else {
        array_push($this->prefixes[$prefix], $base_dir);
    }
}

/**
 * 如果文件存在，从文件系统中加载他到运行环境中.
 *
 * @param string $file 要加在的文件.
 * @return bool 文件存在返回true，否在返回false.
 */
protected function requireFile($file)
{
    if (file_exists($file)) {
        require $file;
        return true;
    }
    return false;
}

}
