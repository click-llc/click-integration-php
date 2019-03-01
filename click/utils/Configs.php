<?php

//  ██████╗██╗     ██╗ ██████╗██╗   ██╗    ██╗    ██╗██████████╗
// ██╔════╝██║     ██║██╔════╝██║ ██╔═╝    ██║    ██║      ██╔═╝
// ██║     ██║     ██║██║     ████╔═╝      ██║    ██║    ██╔═╝
// ██║     ██║     ██║██║     ██║ ██╗      ██║    ██║  ██══╝
// ╚██████╗███████╗██║╚██████╗██║   ██╗ ██╗█████████║██████████╗
//  ╚═════╝╚══════╝╚═╝ ╚═════╝╚═╝   ╚═╝ ╚═╝╚════════╝╚═════════╝

namespace click\utils;

/**
 * @name Configs class
 */
class Configs{
    /** @var configs array-like */
    private $configs;

    /**
     * Configs constructor
     */
    public function __construct(){
        $path_to_configs = __DIR__ . '//..//configs.php';
        $this->configs = require($path_to_configs);
    }

    /**
     * @name get_provider_configs method
     * @return result array-like
     */
    public function get_provider_configs(){
        return $this->configs['provider'];
    }

    /**
     * @name get_database_configs method
     * @return result array-like
     */
    public function get_database_configs(){
        return $this->configs['db'];
    }
}