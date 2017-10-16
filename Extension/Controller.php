<?php
namespace Extension;
/**
 * Created by PhpStorm.
 * User: Johans
 * Date: 2017/9/5
 * Time: 17:25
 */
class Controller{

    //The Model hold by Controller class,to make the accessing database operation more smoothly;
    protected static $model = null;
    public final function  __construct()
    {
        self::model();
    }
    public static function model(...$args) {
        $clsname = static::class;
        $short_clsname =basename(str_replace('\\','/',$clsname));
        $modelname = '';
        if ($short_clsname === 'Controller') {
            $namespace = __NAMESPACE__;
            $modelname =  $namespace.'\\'.'Model';
        } else {
            $clspos = mb_stripos($short_clsname,'Controller');
            $bclsname = $clspos === false ?$short_clsname:mb_substr($short_clsname,0,$clspos);
            $namespace = 'Model';
            $modelname = $namespace.'\\'.$bclsname.'Model';
        }
        self::$model = new $modelname(...$args);
        return self::$model;
    }

}
