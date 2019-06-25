<?php

//  ██████╗██╗     ██╗ ██████╗██╗   ██╗    ██╗    ██╗██████████╗
// ██╔════╝██║     ██║██╔════╝██║ ██╔═╝    ██║    ██║      ██╔═╝
// ██║     ██║     ██║██║     ████╔═╝      ██║    ██║    ██╔═╝
// ██║     ██║     ██║██║     ██║ ██╗      ██║    ██║  ██══╝
// ╚██████╗███████╗██║╚██████╗██║   ██╗ ██╗█████████║██████████╗
//  ╚═════╝╚══════╝╚═╝ ╚═════╝╚═╝   ╚═╝ ╚═╝╚════════╝╚═════════╝

namespace click\models;

/**
 * @name BasicPaymentErrors class, this class can help you for checking the prepare and complete errors
 * 
 * @example
 *      $payments = new Payments();
 *      $response = $payments->request_check($request);
 */
class BasicPaymentsErrors {
    /**
     * @name request_check
     * @param request array-like
     * @return array-like
     */
    public function request_check($request){
        // check to error in request from click
        if($this->is_not_possible_data($request)){
            // return response array-like
            return [
                'error' => -9,
                'error_note' => 'Error in request from click'
            ];
        }

        // prepare sign string as md5 digest
        $sign_string = md5(
            $request['click_trans_id'] .
            $request['service_id'] .
            $this->provider['secret_key'] .
            $request['merchant_trans_id'] .
            ($request['action'] == 1 ? $request['merchant_prepare_id'] : '') .
            $request['amount'] .
            $request['action'] .
            $request['sign_time']
        );
        // check sign string to possible
        if($sign_string != $request['sign_string']){
            // return response array-like
            return [
                'error' => -1,
                'error_note' => 'SIGN CHECK FAILED!'
            ];
        }

        // check to action not found error
        if (!((int)$request['action'] == 0 || (int)$request['action'] == 1)) {
            // return response array-like
            return [
                'error' => -3,
                'error_note' => 'Action not found'
            ];
        }

        // get payment data by merchant_trans_id
        $payment = $this->model->find_by_merchant_trans_id($request['merchant_trans_id']);

        if(!$payment){
            // return response array-like
            return [
                'error' => -5,
                'error_note' => 'User does not exist'
            ];
        }

        // get payment data by merchant_prepare_id
        if( $request['action'] == 1 ) {
            $payment = $this->model->find_by_id($request['merchant_prepare_id']);
            if(!$payment){
                // return response array-like
                return [
                    'error' => -6,
                    'error_note' => 'Transaction does not exist	'
                ];
            }
        }


        // check to already paid
        if($payment['status'] == PaymentsStatus::CONFIRMED){
            // return response array-like
            return [
                'error' => -4,
                'error_note' => 'Already paid'
            ];
        }

        // check to correct amount
        if(abs((float)$payment['total'] - (float)$request['amount']) > 0.01){
            // return response array-like
            return [
                'error' => -2,
                'error_note' => 'Incorrect parameter amount'
            ];
        }

        // check status to transaction cancelled
        if($payment['status'] == PaymentsStatus::REJECTED){
            // return response array-like
            return [
                'error' => -9,
                'error_note' => 'Transaction cancelled'
            ];
        }

        // return response array-like as success
        return [
            'error' => 0,
            'error_note' => 'Success'
        ];

    }
    /**
     * @name is_not_possible_data, this method used in request_check
     * @param request array-like
     * @return respone boolean 
     */
    private function is_not_possible_data($request){
        if(!(
            isset($request['click_trans_id']) &&
            isset($request['service_id']) &&
            isset($request['merchant_trans_id']) &&
            isset($request['merchant_prepare_id']) &&
            isset($request['amount']) &&
            isset($request['action']) &&
            isset($request['error']) &&
            isset($request['error_note']) &&
            isset($request['sign_time']) &&
            isset($request['sign_string']) &&
            isset($request['click_paydoc_id'])
        ) || ((int)$request['action'] > 0 && !isset($request['merchant_prepare_id']))){
            return true;
        }
        return false;
    }
}
