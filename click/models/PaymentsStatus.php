<?php

//  ██████╗██╗     ██╗ ██████╗██╗   ██╗    ██╗    ██╗██████████╗
// ██╔════╝██║     ██║██╔════╝██║ ██╔═╝    ██║    ██║      ██╔═╝
// ██║     ██║     ██║██║     ████╔═╝      ██║    ██║    ██╔═╝
// ██║     ██║     ██║██║     ██║ ██╗      ██║    ██║  ██══╝
// ╚██████╗███████╗██║╚██████╗██║   ██╗ ██╗█████████║██████████╗
//  ╚═════╝╚══════╝╚═╝ ╚═════╝╚═╝   ╚═╝ ╚═╝╚════════╝╚═════════╝

namespace click\models;

/**
 * @name PaymentsStatus class, the some std payment status
 * 
 * @example
 *      if($payment['status'] == PaymentsStatus::CONFIRMED){
 *          ...
 *      }
 */
class PaymentsStatus{
    /** @var INPUT string */
    const INPUT = 'input';
    /** @var WAITING string */
    const WAITING = 'waiting';
    /** @var PREAUTH string */
    const PREAUTH = 'preauth';
    /** @var CONFIRMED string */
    const CONFIRMED = 'confirmed';
    /** @var REJECTED string */
    const REJECTED  = 'rejected';
    /** @var REFUNDED string */
    const REFUNDED = 'refunded';
    /** @var ERROR string */
    const ERROR = 'error';
}