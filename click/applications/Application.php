<?php

//  ██████╗██╗     ██╗ ██████╗██╗   ██╗    ██╗    ██╗██████████╗
// ██╔════╝██║     ██║██╔════╝██║ ██╔═╝    ██║    ██║      ██╔═╝
// ██║     ██║     ██║██║     ████╔═╝      ██║    ██║    ██╔═╝
// ██║     ██║     ██║██║     ██║ ██╗      ██║    ██║  ██══╝
// ╚██████╗███████╗██║╚██████╗██║   ██╗ ██╗█████████║██████████╗
//  ╚═════╝╚══════╝╚═╝ ╚═════╝╚═╝   ╚═╝ ╚═╝╚════════╝╚═════════╝

namespace click\applications;

use click\utils\Helper;
use click\utils\Configs;
use click\exceptions\ClickException;

/**
 * @name Application class, this rest api application
 * 
 * @example:
 * Application::session('YOUR_AUTH_TOKEN', ['/prepare', '/complete'], function(){
 *      $model = new Payments();
 *      (new Application(['model' => $model]))->run();
 * });
 */

class Application{
    /** @var params array-like, it has need included the basic params */
    private $params;
    /** @var helper \click\utils\Helper object, it has included same helpfull methods */
    private $helper;
    /** @var configs \click\utils\Configs object */
    private $configs;

    /**
     * @name Application
     * Application constructor
     * 
     * @param params array-like, the basic configuration
     * @return response|ClickException
     */
    public function __construct($params = null){
        // set content-type as json application
        header('Content-Type: application/json; charset=UTF-8');
        // set configs
        $this->configs = new Configs();
        // set params
        $this->params = $params;
        $this->helper = new Helper();
    }

    /**
     * @name requestHandler method, it capable to detect the request type as router
     * 
     * @param model \click\models\Payments object
     * @return response|ClickException
     */
    private function requestHandler($model){
        // running possibe method via url
        switch($this->helper->url){
            case '/prepare':
                // getting the response of prepare method
                $this->response($model->prepare()); break;
            case '/complete':
                // getting the response of complete method
                $this->response($model->complete()); break;
            case '/payment':
                // getting the response of auto method type detection method
                $this->response($model->payment()); break;
            case '/invoice/create':
                // getting the response of create invoice method
                $this->response($model->create_invoice()); break;
            case '/invoice/check':
                // getting the response of click invoice method
                $this->response($model->check_invoice()); break;
            case '/payment/status':
                // getting the response of click payment id method
                $this->response($model->check_payment_id()); break;
            case '/payment/merchant_train_id':
                // getting the response of merchant trans id
                $this->response($model->merchant_trans_id()); break;
            case '/cancel':
                // getting the response of cancel method
                $this->response($model->cancel()); break;
            case '/card/create':
                // getting the response of create card token method
                $this->response($model->create_card_token()); break;
            case '/card/verify':
                // getting the response of verify card token method
                $this->response($model->verify_card_token()); break;
            case '/card/payment':
                // getting the response of payment with card token method
                $this->response($model->payment_with_card_token()); break;
            case '/card/delete':
                // getting the response of delete card token method
                $this->response($model->delete_card_token()); break;
            default:
                // return exception
                throw new ClickException('Incorrect request', ClickException::ERROR_METHOD_NOT_FOUND);
                break;
        }
    }

    /**
     * @name run method
     * The mean method to start the application
     * 
     * @example:
     *      $application = new Application();
     *      $application->run();
     */
    public function run(){
        if(isset($this->params['model'])){
            // getting model from params
            $model = $this->params['model'];
            $provider_configs = $this->configs->get_provider_configs();
            if(isset($provider_configs['click'])){
                // getting click configs from params
                $click = $provider_configs['click'];
                // initalization click configs to model as provider
                $model->init_provider($click);
                // starting the request handler
                $this->requestHandler($model);
            }
            else{
                // return the exception
                throw new ClickException(
                    'Could not run the application without click configuration',
                    ClickException::ERROR_INSUFFICIENT_PRIVILEGE
                );
            }
        }
        else{
            // return the exception
            throw new ClickException(
                'Could not run the application without model',
                ClickException::ERROR_INSUFFICIENT_PRIVILEGE
            );
        }
    }

    /**
     * @name session method, is the mean method to running your application with auth token and
     * fully json application
     * @param token string, the authroization token
     * @param access array-like, the urls to accessable methods without authtorization token
     * @param func function, the session body
     * 
     * @example:
     * Application::session('YOUR_AUTH_TOKEN', ['/prepare', '/complete'], function(){
     *      ...
     * });
     */
    public static function session($token, $access, $func){
        // error handler
        try{
            // getting request headers
            $headers = apache_request_headers();
            // used the \click\utils\Helper object
            $helper = new Helper();
            // check the request url to accessable
            if(in_array($helper->url, $access)){
                // calling the session body function
                if(is_callable($func))
                    $func();
            }
            // check the Auth to seted
            else if(isset($headers['Auth'])){
                $auth_token = $headers['Auth'];
                // check the authtoization token is possible
                if($token == $auth_token){
                    // call the session body function
                    if(is_callable($func))
                        $func();
                }
                else{
                    // return exception
                    throw new ClickException(
                        'Authorization error',
                        ClickException::ERROR_INTERNAL_SYSTEM
                    );
                }
            }
            else{
                // return exception
                throw new ClickException(
                    'Session could not perform without Auth token',
                    ClickException::ERROR_INTERNAL_SYSTEM
                );
            }
        }
        catch(ClickException $e){
            // display the error
            print_r(json_encode($e->error(), JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * @name response method, it capable the display the result as json format
     * @param data array-like, the response to display
     * 
     * @example:
     *      $this->response([
     *          ...
     *      ]);
     */
    private function response($data){
        print_r(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}