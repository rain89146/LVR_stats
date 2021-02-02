<?php 
    spl_autoload_register('AutoLoader');

    function AutoLoader($classname) {
        $path = "classes/";
        $ext = ".class.php";
        $full_path = $path . $classname . $ext;
        include $full_path;
    }
?>