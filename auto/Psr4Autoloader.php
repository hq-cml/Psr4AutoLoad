<?php
namespace auto;
/**
 * An example of a general-purpose implementation that includes the optional
 * functionality of allowing multiple base directories for a single namespace
 * prefix.
 * 下面例子中在一个命名空间前缀下有多个base目录。
 *
 * Given a foo-bar package of classes in the file system at the following
 * paths ...
 * 在下面路径中foo-bar包中存在以下类：
 *
 *     /path/to/packages/foo-bar/
 *         src/
 *             Baz.php             # Foo\Bar\Baz
 *             Qux/
 *                 Quux.php        # Foo\Bar\Qux\Quux
 *         tests/
 *             BazTest.php         # Foo\Bar\BazTest
 *             Qux/
 *                 QuuxTest.php    # Foo\Bar\Qux\QuuxTest
 *
 * ... add the path to the class files for the \Foo\Bar\ namespace prefix
 * as follows:
 * ...对\Foo\Bar\命名空间前缀，添加类文件的路径
 *
 *      <?php
 *      // instantiate the loader
 *      // 初始化loader
 *      $loader = new \Example\Psr4AutoloaderClass;
 *
 *      // register the autoloader
 *      // 注册autoloader
 *      $loader->register();
 *
 *      // register the base directories for the namespace prefix
 *      // 注册命名空间前缀的多个base目录
 *      $loader->addNamespace('Foo\Bar', '/path/to/packages/foo-bar/src');
 *      $loader->addNamespace('Foo\Bar', '/path/to/packages/foo-bar/tests');
 *
 * The following line would cause the autoloader to attempt to load the
 * \Foo\Bar\Qux\Quux class from /path/to/packages/foo-bar/src/Qux/Quux.php:
 * 下面代码将用/path/to/packages/foo-bar/src/Qux/Quux.php文件来加载\Foo\Bar\Qux\Quux类。
 *
 *      <?php
 *      new \Foo\Bar\Qux\Quux;
 *
 * The following line would cause the autoloader to attempt to load the
 * \Foo\Bar\Qux\QuuxTest class from /path/to/packages/foo-bar/tests/Qux/QuuxTest.php:
 * 下面代码将用/path/to/packages/foo-bar/tests/Qux/QuuxTest.php文件来加载
 * \Foo\Bar\Qux\QuuxTest类。
 *
 *      <?php
 *      new \Foo\Bar\Qux\QuuxTest;
 */
class Psr4AutoLoader {
    /**
     * 命名空间前缀和具体路径对应的映射表(这在composer中有相同的东西)
     * 一个命名空间前缀中可以对应多个base路径的哦亲～
     */
    protected $prefixes = array();

    /**
     * 注册加载函数到自动加载函数栈中
     *
     * @return void
     */
    public function register() {
        spl_autoload_register(array($this,
            'loadClass'));
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
    public function addNamespace($prefix, $base_dir, $prepend = false) {
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
    protected function requireFile($file) {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }

    /**
     * 加载命名空间前缀和relative class(相对类)映射的文件.
     *
     * @param string $prefix 命名空间前缀.
     * @param string $relative_class relative class名称.
     * @return mixed 成功返回映射的文件路径，失败返回false.
     */
    protected function loadMappedFile($prefix, $relative_class) {
        // 命名空间前缀数组中不存在prefix命名空间前缀，返回false.
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }

        // look through base directories for this namespace prefix
        // 遍历命名空间前缀对应的目录数组，知道找到映射的文件
        foreach ($this->prefixes[$prefix] as $base_dir) {

            // 用具体路径替换掉命名空间前缀,
            // 替换relative class中的命名空间分隔符为目录分隔符
            // 添加.php后缀
            $file = $base_dir
                . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class)
                . '.php';

            // 如果映射文件存在加载对应的文件
            if ($this->requireFile($file)) {
                // 返回成功加载的文件路径
                return $file;
            }
        }

        // 未找到要映射的文件返回false
        return false;
    }

    /**
     * 加载给定类的对应的类库文件
     *
     * @param string $class 完整的类库名称.
     * @return mixed 成功时返回类名对应的类库文件路径，失败时返回false.
     */
    public function loadClass($class) {
        // 当前的命名空间前缀
        $prefix = $class;

        //通过命名空间去查找对应的文件,注意此处是strrpos,从右侧开始扫描~
        while (false !== $pos = strrpos($prefix, '\\')) {

            // 可能存在的命名空间前缀
            $prefix = substr($class, 0, $pos + 1);

            // 剩余部分是可能存在的类
            $relative_class = substr($class, $pos + 1);

            //试图加载prefix前缀和relitive class对应的文件
            $mapped_file = $this->loadMappedFile($prefix, $relative_class);
            if ($mapped_file) {
                return $mapped_file;
            }

            // 移动命名空间和relative class分割位置到下一个位置
            $prefix = rtrim($prefix, '\\');
        }

        // 未找到试图加载的文件
        return false;
    }
}
