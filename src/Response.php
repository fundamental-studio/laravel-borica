<?php

    namespace Borica;

    use Carbon\Carbon;

    class Response
    {
        const TRANSACTION_CODE = 'TRANSACTION_CODE';
		const TRANSACTION_TIME = 'TRANSACTION_TIME';
		const AMOUNT = 'AMOUNT';
		const TERMINAL_ID = 'TERMINAL_ID';
		const ORDER_ID = 'ORDER_ID';
		const RESPONSE_CODE = 'RESPONSE_CODE';
		const PROTOCOL_VERSION = 'PROTOCOL_VERSION';
		const SIGN = 'SIGN';
        const SIGNATURE_OK = 'SIGNATURE_OK';
        
        private $response = [];
        private $publicCert;
        
        public function __construct()
        {
            $this->publicCert = config('borica.cert');
        }

        public function parse($message) : Borica
        {
            $message = base64_decode($message);
            $messageSign = substr($message, 56, 128);

            $this->response = [
                self::TRANSACTION_CODE => substr($message, 0, 2),
                self::TRANSACTION_TIME => substr($message, 2, 14),
                self::AMOUNT => substr($message, 16, 12),
                self::TERMINAL_ID => substr($message, 28, 8),
                self::ORDER_ID => substr($message, 36, 15),
                self::RESPONSE_CODE => substr($message, 51, 2),
                self::PROTOCOL_VERSION => substr($message, 53, 3),
                self::SIGN => $messageSign,
                self::SIGNATURE_OK => $this->verify($message, $messageSign)
            ];

            return $this;
        }

        protected function verify($message, $sign)
        {
            $publicKey = openssl_get_fppublickey($this->getCertificate());
            $verification = openssl_verify(substr($message, 0, strlen($message) - 128), $sign, $publicKey);
            openssl_free_key($publicKey);

            return $verification;
        }

        protected function getCertificate() : String
        {
            $fp = fopen($this->publicCert, 'r');
            $cert = fread($fp, 8192);
            fclose($fp);

            return $cert;
        }

        public function getTransactionCode()
        {
            return $this->response[self::TRANSACTION_CODE];
        }
        
        public function getTransactionTime() : Carbon
        {
            return Carbon::createFromFormat('YmdHis', $this->response[self::TRANSACTTION_TIME]);
        }

        public function getAmount()
        {
            return (float) $this->response[self::AMOUNT] / 100;
        }

        public function getTerminalID()
        {
            return $this->response[self::TERMINAL_ID];
        }

        public function getOrderID()
        {
            return $this->response[self::ORDER_ID];
        }

        public function getResponseCode()
        {
            return $this->response[self::RESPONSE_CODE];
        }

        public function isSuccessful()
        {
            return (bool) $this->getResponseCode() === '00';
        }

        public function get()
        {
            return $this->response;
        }
    }