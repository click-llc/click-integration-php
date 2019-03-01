<?php

//  ██████╗██╗     ██╗ ██████╗██╗   ██╗    ██╗    ██╗██████████╗
// ██╔════╝██║     ██║██╔════╝██║ ██╔═╝    ██║    ██║      ██╔═╝
// ██║     ██║     ██║██║     ████╔═╝      ██║    ██║    ██╔═╝
// ██║     ██║     ██║██║     ██║ ██╗      ██║    ██║  ██══╝
// ╚██████╗███████╗██║╚██████╗██║   ██╗ ██╗█████████║██████████╗
//  ╚═════╝╚══════╝╚═╝ ╚═════╝╚═╝   ╚═╝ ╚═╝╚════════╝╚═════════╝

namespace click\models;

use \PDO;

/**
 * @name Model class, this can help you for connecting, reading, writing, updating the payments
 * 
 * @example
 *      $model   = new Model();
 *      $payment = $model->find_by_token('aaaa-bbbb-cccc-ddddddddd');
 */
class Model{
    /** @var params array-like, it has need included the database configurations */
    private $params;
    /** @var conn PDO object, it will be helpfull for connect to database */
    private $conn;
    /** @var configs array-like */
    private $configs;

    /**
     * Payments constructor
     * @param params array-like, the db configuration
     */
    public function __construct($params = null){
        // set db params
        $this->params = $params['db'];
        // connection to database
        $this->conn   = $this->connect();
        // set the configurations
        $this->configs = null;
        if(isset($params['configs'])){
            $this->configs = $params['configs'];
        }
    }

    /**
     * @name connect method, the mean method for connection to database and this
     * called in contructor
     * @return PDO object
     */
    private function connect(){
        // make the PDO connection object
        $conn = new PDO($this->params['dsn'], $this->params['username'], $this->params['password']);
        // set attributes
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // return PDO object
        return $conn;
    }

    /**
     * @name find_by_id, this method can find the payment data by id
     * @param payment_id integer
     * @return response array-like
     * 
     * @example:
     *      $model = new Model();
     *      $payment = $model->find_by_id(1111);
     */
    public function find_by_id($payment_id){
        // make sql query
        $query = 'SELECT * FROM `payments` WHERE id = ' . $payment_id;
        // prepare the query to execute
        $statement = $this->conn->prepare($query);
        // execute the statement
        $statement->execute();
        $result = $statement->setFetchMode(\PDO::FETCH_ASSOC);
        // return response array-like
        return $statement->fetch();
    }
    /**
     * @name find_by_id, this method can find the payment data by id
     * @param token string
     * @return response array-like
     * 
     * @example:
     *      $model = new Model();
     *      $payment = $model->find_by_token('aaaa-bbbb-cccc-dddddddd');
     */
    public function find_by_token($token){
        // make sql query
        $query = 'SELECT * FROM `payments` WHERE token = "' . $payment_id . '"';
        // prepare the query to execute
        $statement = $this->conn->prepare($query);
        // execute the statement
        $statement->execute();
        $result = $statement->setFetchMode(\PDO::FETCH_ASSOC);
        // return response array-like
        return $statement->fetch();
    }

    /**
     * @name find_by_merchant_trans_id, this method can find the payment data by merchant_trans_id
     * @param merchant_trans_id integer
     * @return response array-like
     * 
     * @example:
     *      $model = new Model();
     *      $payment = $model->find_by_merchant_trans_id(2222);
     */
    public function find_by_merchant_trans_id($merchant_trans_id){
        // make sql query
        $query = 'SELECT * FROM `payments` WHERE merchant_trans_id = ' . $merchant_trans_id;
        // prepare the query to execute
        $statement = $this->conn->prepare($query);
        // execute the statement
        $statement->execute();
        $result = $statement->setFetchMode(\PDO::FETCH_ASSOC);
        // return response
        return $statement->fetch();
    }

    /**
     * @name update_by_id, this method can update the payment in databse by id
     * @param payment_id integer
     * @param arguments array-like
     * @return response array-like
     * 
     * @example:
     *      $model = new Model();
     *      $model->update_by_id(1111, [
     *          ...
     *      ]);
     */
    public function update_by_id($payment_id, $arguments = []){
        // function query sets maker
        function sets($arguments){
            $result = [];
            foreach($arguments as $key => $value){
                // check value to null
                if($value == null){
                    array_push($result, "$key = NULL");
                }
                else{
                    array_push($result, "$key='$value'");
                }
            }
            // add the modified
            array_push($result, 'modified=\'' . date("Y-m-d H:i:s") . '\'');
            // return response
            return implode(', ', $result);
        }
        // make sets
        $sets = sets($arguments);
        // make query
        $query = "Update `payments` SET $sets WHERE id = $payment_id";
        // prepare query to execute
        $statement = $this->conn->prepare($query);
        // execute the statement
        $statement->execute();
        // return response array-like
        return $statement->rowCount();
    }

    /**
     * @name update_by_id, this method can update the payment in databse by id
     * @param token string
     * @param arguments array-like
     * @return response array-like
     * 
     * @example:
     *      $model = new Model();
     *      $model->update_by_token('aaaa-bbbb-cccc-dddddddddd', [
     *          ...
     *      ]);
     */
    public function update_by_token($token, $arguments){
        // function query sets maker
        function sets($arguments){
            $result = [];
            foreach($arguments as $key => $value){
                // check value to null
                if($value == null){
                    array_push($result, "$key = NULL");
                }
                else{
                    array_push($result, "$key='$value'");
                }
            }
            // add the modified
            array_push($result, 'modified=\'' . date("Y-m-d H:i:s") . '\'');
            // return response
            return implode(', ', $result);
        }

        // make sets
        $sets = sets($arguments);
        // make query
        $query = "Update `payments` SET $sets WHERE token = '$token'";
        // prepare query to execute
        $statement = $this->conn->prepare($query);
        // execute the statement
        $statement->execute();
        // return response array-like
        return $statement->rowCount();
    }
}