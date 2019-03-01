<?php

//  ██████╗██╗     ██╗ ██████╗██╗   ██╗    ██╗    ██╗██████████╗
// ██╔════╝██║     ██║██╔════╝██║ ██╔═╝    ██║    ██║      ██╔═╝
// ██║     ██║     ██║██║     ████╔═╝      ██║    ██║    ██╔═╝
// ██║     ██║     ██║██║     ██║ ██╗      ██║    ██║  ██══╝
// ╚██████╗███████╗██║╚██████╗██║   ██╗ ██╗█████████║██████████╗
//  ╚═════╝╚══════╝╚═╝ ╚═════╝╚═╝   ╚═╝ ╚═╝╚════════╝╚═════════╝

namespace click\exceptions;

/**
 * @name ClickEception class
 * @example
 *      throw new ClickException(
 *          'THE_DESCRIPTION_OF_EXCEPTION',
 *          THE_CODE_OF_EXCEPTION
 *      );
 */
class ClickException extends \Exception{
    /** @var ERROR_INTERNAL_SYSTEM */
    const ERROR_INTERNAL_SYSTEM = -32400;
    /** @var ERROR_INSUFFICIENT_PRIVILEGE */
    const ERROR_INSUFFICIENT_PRIVILEGE = -32504;
    /** @var ERROR_INVALID_JSON_RPC_OBJECT */
    const ERROR_INVALID_JSON_RPC_OBJECT = -32600;
    /** @var ERROR_METHOD_NOT_FOUND */
    const ERROR_METHOD_NOT_FOUND = -32601;
    /** @var ERROR_INVALID_AMOUNT */
    const ERROR_INVALID_AMOUNT = -31001;
    /** @var ERROR_TRANSACTION_NOT_FOUND */
    const ERROR_TRANSACTION_NOT_FOUND = -31003;
    /** @var ERROR_INVALID_ACCOUNT */
    const ERROR_INVALID_ACCOUNT = -31050;
    /** @var ERROR_COULD_NOT_CANCEL */
    const ERROR_COULD_NOT_CANCEL = -31007;
    /** @var ERROR_COULD_NOT_PERFORM */
    const ERROR_COULD_NOT_PERFORM = -31008;
    /** @var error array-like */
    public $error;

    /**
     * ClickException contructor
     * @param error_note string
     * @param error_code integer
     */
    public function __construct($error_note, $error_code)
    {
        $this->error_note = $error_note;
        $this->error_code = $error_code;

        $this->error = ['error_code' => $this->error_code];

        if ($this->error_note) {
            $this->error['error_note'] = $this->error_note;
        }
    }

    /**
     * @name error method
     * @return error array-like
     */
    public function error()
    {
        return $this->error;
    }
}