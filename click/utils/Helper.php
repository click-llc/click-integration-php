<?php

//  ██████╗██╗     ██╗ ██████╗██╗   ██╗    ██╗    ██╗██████████╗
// ██╔════╝██║     ██║██╔════╝██║ ██╔═╝    ██║    ██║      ██╔═╝
// ██║     ██║     ██║██║     ████╔═╝      ██║    ██║    ██╔═╝
// ██║     ██║     ██║██║     ██║ ██╗      ██║    ██║  ██══╝
// ╚██████╗███████╗██║╚██████╗██║   ██╗ ██╗█████████║██████████╗
//  ╚═════╝╚══════╝╚═╝ ╚═════╝╚═╝   ╚═╝ ╚═╝╚════════╝╚═════════╝

namespace click\utils;

use click\utils\Configs;

/**
 * @name Helper class, this has included the some basic methods
 * 
 * @example
 *      $helper = new Helper();
 *      $helper->check_phone_number('998901112233');
 */
class Helper{
    /** @var endpoint string */
    public  $endpoint = 'https://api.click.uz/v2/merchant/';
    /** @var script_name string */
    private $script_name;
    /** @var request_url string */
    private $request_url;
    /** @var method string */
    public  $method;
    /** @var timestamp integer */
    public  $timestamp;
    /** @var url string */
    public  $url;
    /** @var configs \click\utils\Configs object */
    private $configs;

    /**
     * Helper constructor
     */
    public function __construct(){
        // set configs
        $this->configs = new Configs();
        $provider_configs = $this->configs->get_provider_configs();
        if(isset($provider_configs['endpoint'])){
            $this->endpoint = $provider_configs['endpoint'];
        }
        // set script name
        $this->script_name = $_SERVER['SCRIPT_NAME'];
        // set request url
        $this->request_url = $_SERVER['REQUEST_URI'];
        // set method
        $this->method      = $_SERVER['REQUEST_METHOD'];
        // set timestamp
        $this->timestamp   = $_SERVER['REQUEST_TIME'];
        // set url from _url method
        $this->url         = $this->_url();
    }

    /**
     * @name _url method, it return the possible url to process the requests
     * 
     * @return response string
     */
    private function _url(){
        return $_SERVER['REQUEST_URI'];

//        $script_name_segments = explode('/', $this->script_name);
//        unset($script_name_segments[count($script_name_segments ) - 1]);
//        $base = implode($script_name_segments, '/');
//        $base = explode($base, $this->request_url);
//        $base = $base[count($base) - 1];
//        $base = explode('?', $base)[0];
//        return $base;
    }

    /**
     * @name check_card_number method, this method check the card number to possible
     * 
     * @param card_number string
     * @return response string|null
     * 
     * @example
     *      $helper = new Helper();
     *      $helper->check_card_number('AAAA-BBBB-CCCC-DDDD');
     */
    public function check_card_number($card_number){
        if(preg_match('/[0-9]{12}/', $card_number)){
            return $card_number;
        }
        if(preg_match('/[0-9]{4}-[0-9]{4}-[0-9]{4}-[0-9]{4}/', $card_number)){
            $card_number = explode('-', $card_number);
            $card_number = implode('', $card_number);
            return $card_number;
        }
        return null;
    }

    /**
     * @name check_phone_number, this method check the phone number to possible
     * 
     * @param phone_number string
     * @return response string|null
     * 
     * @example
     *      $helper = new Helper();
     *      $helper->check_phone_number('99801112233');
     */
    public function check_phone_number($phone_number){
        if(strlen($phone_number) != 0 && $phone_number[0] == '+'){
            $phone_number = substr($phone_number, 1, strlen($phone_number));
            if(preg_match('/[0-9]{12}/', $phone_number)){
                return $phone_number;
            }
            return null;
        }
        if(preg_match('/[0-9]{12}/', $phone_number)){
            return $phone_number;
        }
        if(preg_match('/[0-9]{9}/', $phone_number)){
            return '998'. $phone_number;
        }
        if(preg_match('/[0-9]{8}/', $phone_number)){
            return '9989' . $phone_number;
        }
        return null;
    }
}