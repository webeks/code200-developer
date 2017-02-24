<?php namespace Code200\Developer;

use Illuminate\Support\Facades\Lang;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function __construct(\Illuminate\Contracts\Foundation\Application $app)
    {
        parent::__construct($app);

        ini_set('xdebug.var_display_max_depth', 3);
        ini_set('xdebug.var_display_max_children', 64);
        ini_set('xdebug.var_display_max_data', 64);
//        ini_set('xdebug.max_nesting_level', 64);


    }

    public function register()
    {
        parent::register();

        $this->registerConsoleCommand('developer.dbbackup', 'Code200\Developer\Console\Dbbackup');
        $this->registerConsoleCommand('developer.gradimtransfercategories', 'Code200\Developer\Console\GradimTransferCategories');
        $this->registerConsoleCommand('developer.gradimtransferposts', 'Code200\Developer\Console\GradimTransferPosts');
        $this->registerConsoleCommand('developer.gradimtransfersearch', 'Code200\Developer\Console\GradimTransferSearch');

    }

//    public function registerComponents()
//    {
//    }
//
//    public function registerSettings()
//    {
//    }


    public function registerMarkupTags()
    {
        return [
//            'filters' => [
//                // A global function, i.e str_plural()
//                'plural' => 'str_plural',
//
//                // A local method, i.e $this->makeTextAllCaps()
//                'uppercase' => [$this, 'makeTextAllCaps']
//            ],
            'functions' => [
                // A static method call, i.e Form::open()
//                'printr' => ['October\Rain\Html\Form', 'open'],
                'php_var_dump' => function($object) {
                    var_dump($object);
                },
                'php_print_r' => function($object, $format = false) {
                    if($format) echo '<pre>';

                    print_r($object);

                    if($format) echo '</pre>';
                },
                //@todo move this -> developer plugin shouldnt be in production
                'UID' => function() {
                        //@todo move this thing to config !!!!
                        $salt = "ljh8o24#$&aDSFGG#T23da%#";

                        function getClientIp() {
                            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                                $ip = $_SERVER['HTTP_CLIENT_IP'];
                            } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                            } else {
                                $ip = $_SERVER['REMOTE_ADDR'];
                            }
                            return $ip;
                        }

                        $ip = getClientIp();
                        $uid = md5($ip . $salt . $_SERVER['HTTP_USER_AGENT']);
                        return $uid;
                }
            ]
        ];
    }



}
