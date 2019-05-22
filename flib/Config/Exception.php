<?php

class Config_Exception extends Exception {
    /**
     * Config_Exception constructor.
     * @param Exception $message
     * @param int $code
     */
    public function __construct($message, $code = 0) {
        if (is_a($message, 'Exception')) {
            parent::__construct($message->getMessage(), intval($message->getCode()));
        } else {
            parent::__construct($message, intval($code));
        }
    }

}
