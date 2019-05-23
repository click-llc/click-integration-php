<?php

//  ██████╗██╗     ██╗ ██████╗██╗   ██╗    ██╗    ██╗██████████╗
// ██╔════╝██║     ██║██╔════╝██║ ██╔═╝    ██║    ██║      ██╔═╝
// ██║     ██║     ██║██║     ████╔═╝      ██║    ██║    ██╔═╝
// ██║     ██║     ██║██║     ██║ ██╗      ██║    ██║  ██══╝
// ╚██████╗███████╗██║╚██████╗██║   ██╗ ██╗█████████║██████████╗
//  ╚═════╝╚══════╝╚═╝ ╚═════╝╚═╝   ╚═╝ ╚═╝╚════════╝╚═════════╝

namespace click\models;

/**
 * @name BasePayments class
 */
class BasePayments extends \click\models\BasicPaymentMethods{
    /**
     * @name get_merchant_trans_id method
     * @param token string
     * @return merchant_trans_id integer
     */
    protected function get_merchant_trans_id($token){
        $data = $this->model->find_by_token($token);
        $id = $data['id'];
        $this->model->update_by_id($id, [
            'merchant_trans_id' => $id
        ]);
        return $id;
    }

    /**
     * @name on_user_is_exists method
     * @param payment array-like
     * @return response boolean|null
     */
    protected function on_user_is_exist($payment){
        return null;
    }

    /**
     * @name on_invoice_creating method
     * @param data array-like
     * @return response \GuzzleHttp\Client
     */
    protected function on_invoice_creating($data){
        $response = $this->client->request('POST', 'invoice/create', [
            'json' => $data
        ]);
        return $response;
    }

    /**
     * @name on_invoice_created method
     * @param request array-like
     * @param response \GuzzleHttp\Client object
     * @param token string
     * @return response array-like|null
     */
    protected function on_invoice_created($request, $response, $token){
        if($response->getStatusCode() == 200){
            $result = (array)json_decode((string) $response->getBody());
            if((int)$result['error_code'] == 0){
                $this->model->update_by_token($token, [
                    'status' => PaymentsStatus::WAITING,
                    'status_note' => $result['error_note'],
                    'invoice_id' => $result['invoice_id'],
                    'phone_number' => $request['phone_number']
                ]);
            }
            else{
                $this->model->update_by_id($payment_id, [
                    'status' => PaymentsStatus::ERROR,
                    'status_note' => $result['error_note']
                ]);
            }
            return $result;
        }
        return null;
    }

    /**
     * @name on_invoice_checking method
     * @param data array-like
     * @return response \GuzzleHttp\Client object
     */
    protected function on_invoice_checking($data){
        $url = 'invoice/status/' . $data['service_id'] . '/' . $data['invoice_id'];
        $response = $this->client->request('GET', $url);
        return $response;
    }

    /**
     * @name on_invoice_checked method
     * @param request array-like
     * @param response \GuzzleHttp\Client object
     * @param token string
     * @return response array-like|null
     */
    protected function on_invoice_checked($request, $response, $token){
        if($response->getStatusCode() == 200){
            $result = (array)json_decode((string) $response->getBody());
            if((int)$result['error_code'] == 0){
                if((int)$result['status'] > 0){
                    $this->model->update_by_token($token, [
                        'status' => PaymentsStatus::CONFIRMED,
                        'status_note' => $result['error_note']
                    ]);
                }
                else if((int)$result['status'] == -99){
                    $this->model->update_by_token($token, [
                        'status' => PaymentsStatus::REJECTED,
                        'status_note' => $result['error_note']
                    ]);
                }
                else{
                    $this->model->update_by_token($token, [
                        'status' => PaymentsStatus::ERROR,
                        'status_note' => $result['error_note']
                    ]);
                }
            }
            return $result;
        }
        return null;
    }

    /**
     * @name on_canceling method
     * @param data array-like
     * @return response \GuzzleHttp\Client object
     */
    protected function on_canceling($data){
        $url = 'payment/reversal/' . $data['service_id'] . '/' . $data['payment_id'];
        $response = $this->client->request('DELETE', $url);
        return $response;
    }

    /**
     * @name on_canceled method
     * @param request array-like
     * @param response \GuzzleHttp\Client object
     * @param token string
     * @return response array-like|null
     */
    protected function on_canceled($request, $response, $token){
        if($response->getStatusCode() == 200){
            $result = (array)json_decode((string) $response->getBody());
            if((int)$result['error_code'] == 0){
                $this->model->update_by_token($token, [
                    'status' => PaymentsStatus::REJECTED,
                    'status_note' => $result['error_note'],
                    'payment_id' => $result['payment_id']
                ]);
            }
            else{
                $this->model->update_by_token($token, [
                    'status' => PaymentsStatus::ERROR,
                    'status_note' => $result['error_note']
                ]);
            }
            return $result;
        }
        return null;
    }

    /**
     * @name on_card_token_creating method
     * @param data array-like
     * @return response \GuzzleHttp\Client object
     */
    protected function on_card_token_creating($data){
        $response = $this->client->request('POST', 'card_token/request', [
            'json' => $data
        ]);
        return $response;
    }

    /**
     * @name on_card_token_created method
     * @param request array-like
     * @param response \GuzzleHttp\Client object
     * @param token string
     * @return response array-like|null
     */
    protected function on_card_token_created($request, $response, $token){
        if($response->getStatusCode() == 200){
            $result = (array)json_decode((string) $response->getBody());
            if((int)$result['error_code'] == 0){
                $this->model->update_by_token($token, [
                    'status' => PaymentsStatus::CONFIRMED,
                    'status_note' => $result['error_note'],
                    'card_token' => $result['card_token'],
                    'phone_number' => $result['phone_number']
                ]);
            }
            else{
                $this->model->update_by_token($token, [
                    'status' => PaymentsStatus::ERROR,
                    'status_note' => $result['error_note']
                ]);
            }
            return $result;
        }
        return null;
    }

    /**
     * @name on_card_token_verifying method
     * @param data array-like
     * @return response \GuzzleHttp\Client object
     */
    protected function on_card_token_verifying($data){
        $response = $this->client->request('POST', 'card_token/verify', [
            'json' => $data
        ]);
        return $response;
    }

    /**
     * @name on_card_token_verified method
     * @param request array-like
     * @param response \GuzzleHttp\Client object
     * @param token string
     * @return response array-like|null
     */
    protected function on_card_token_verified($request, $response, $token){
        if($response->getStatusCode() == 200){
            $result = (array)json_decode((string) $response->getBody());
            if((int)$result['error_code'] == 0){
                $this->model->update_by_token($token, [
                    'status' => PaymentsStatus::CONFIRMED,
                    'status_note' => $result['error_note'],
                    'card_number' => $result['card_number']
                ]);
            }
            else{
                $this->model->update_by_token($token, [
                    'status' => PaymentsStatus::ERROR,
                    'status_note' => $result['error_note']
                ]);
            }
            return $result;
        }
        return null;
    }

    /**
     * @name on_card_token_paying method
     * @param data array-like
     * @return response \GuzzleHttp\Client object
     */
    protected function on_card_token_paying($data){
        $response = $this->client->request('POST', 'card_token/payment', [
            'json' => $data
        ]);
        return $response;
    }

    /**
     * @name on_card_token_payed method
     * @param request array-like
     * @param response \GuzzleHttp\Client object
     * @param token string
     * @return response array-like|null
     */
    protected function on_card_token_payed($request, $response, $token){
        if($response->getStatusCode() == 200){
            $result = (array)json_decode((string) $response->getBody());
            if((int)$result['error_code'] == 0){
                $this->model->update_by_token($token, [
                    'status' => PaymentsStatus::CONFIRMED,
                    'status_note' => $result['error_note'],
                    'payment_id' => $result['payment_id']
                ]);
            }
            else{
                $this->model->update_by_token($token, [
                    'status' => PaymentsStatus::ERROR,
                    'status_note' => $result['error_note']
                ]);
            }
            return $result;
        }
        return null;
    }

    /**
     * @name on_card_token_deleting method
     * @param data array-like
     * @return response \GuzzleHttp\Client object
     */
    protected function on_card_token_deleting($data){
        $url = 'card_token/' . $data['service_id'] . '/' . $data['card_token'];
        $response = $this->client->request('DELETE', $url);
        return $response;
    }

    /**
     * @name on_card_token_deleted method
     * @param request array-like
     * @param response \GuzzleHttp\Client object
     * @param token string
     * @return response array-like|null
     */
    protected function on_card_token_deleted($request, $response, $token){
        if($response->getStatusCode() == 200){
            $result = (array)json_decode((string) $response->getBody());
            if((int)$result['error_code'] == 0){
                $this->model->update_by_token($token, [
                    'card_id' => null,
                    'status_note' => $result['error_note']
                ]);
            }
            else{
                $this->model->update_by_token($token, [
                    'status' => PaymentsStatus::ERROR,
                    'status_note' => $result['error_note']
                ]);
            }
            return $result;
        }
        return null;
    }

    /**
     * @name on_payment_checking method
     * @param data array-like
     * @return response \GuzzleHttp\Client object
     */
    protected function on_payment_checking($data){
        $url = 'payment/status/' . $data['service_id'] . '/' . $data['payment_id'];
        $response = $this->client->request('GET', $url);
        return $response;
    }

    /**
     * @name on_payment_checked method
     * @param request array-like
     * @param response \GuzzleHttp\Client object
     * @param token string
     * @return response array-like|null
     */
    protected function on_payment_checked($request, $response, $token){
        return $this->on_invoice_checked($request, $response, $token);
    }

    /**
     * @name on_with_merchant_trans_id method
     * @param data array-like
     * @return response \GuzzleHttp\Client object
     */
    protected function on_checking_with_merchant_trans_id($data){
        $url = 'payment/status_by_mti/' . $data['service_id'] . '/' . $data['merchant_trans_id'];
        $response = $this->client->request('DELETE', $url);
        return $response;
    }

    /**
     * @name on_with_merchant_trans_id
     * @param request array-like
     * @param response array-like
     * @param token string
     * @return response array-like|null
     */
    protected function on_checked_with_merchant_trans_id($request, $response, $token){
        if($response->getStatusCode() == 200){
            $result = (array)json_decode((string) $response->getBody());
            if((int)$result['error_code'] == 0){
                $this->model->update_by_token($token, [
                    'payment_id' => $result['payment_id'],
                    'merchant_trans_id' => $result['merchant_trans_id'],
                    'status_note' => $result['error_note']
                ]);
            }
            else{
                $this->model->update_by_token($token, [
                    'status' => PaymentsStatus::ERROR,
                    'status_note' => $result['error_note']
                ]);
            }
            return $result;
        }
        return null;
    }

}
