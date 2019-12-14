<?php
    
    namespace Borica;

    use Carbon\Carbon;
    use Borica\Exceptions\InvalidAmountException;
    use Borica\Exceptions\UnsupportedLanguageException;
    use Borica\Exceptions\UnsupportedCurrencyException;
    use Borica\Exceptions\UnkownTransactionCodeException;
    use Borica\Exceptions\InvalidProtocolVersionException;

    class Request
    {
        private $gatewayUrl = 'https://gate.borica.bg/boreps/';
        private $testGatewayUrl = 'https://gatet.borica.bg/boreps/';

        private $transactionCode;
        private $amount;
        private $terminalID;
        private $orderID;
        private $orderDescription;
        private $language = 'BG';
        private $protocolVersion = '1.1';
        private $currency = 'BGN';
        private $ott = null;
        private $dt = null;

        private $isProduction = false;

        private $message = [];

        const PROTOCOL_VERSIONS = ['1.0', '1.1', '2.0'];
        const SUPPORTED_LANGUAGES = ['BG', 'EN'];
        const SUPPORTED_CURRENCIES = ['BGN', 'USD', 'EUR'];
        const TRANSACTION_CODES = [
            '10' => 'registerTransaction',
            '11' => 'payProfit',
            '21' => 'delayedAuthorizationRequest',
            '22' => 'delayedAuthorizationComplete',
            '23' => 'delayedAuthorizationReversal',
            '40' => 'reversal',
            '41' => 'payedProfitReversal'
        ];

        public function __construct($language = null, $currency = null, $protocolVersion = null, $ott = null)
        {
            $this->terminalID = config('borica.terminal_id');
            $this->isProduction = config('borica.environment');

            $this->privateKey = config('borica.pk');
            $this->privateKeyPass = config('borica.pk_pass');
            
            $this->protocolVersion = $this->protocol($protocolVersion);
            $this->ott = $this->ott($ott);

            $this->language = $this->language($language);
            $this->currency = $this->currency($currency);
        }

        protected function generateMessage()
        {
            $protocolVersion = $this->getProtocolVersion();

            $data = [
                $this->getTransactionCode(),
                $this->getDateTime(),
                $this->getAmount(),
                $this->getTerminalID(),
                $this->getOrderID(),
                $this->getOrderDescription(),
                $this->getLanguage(),
                $protocolVersion
            ];

            if ($protocolVersion != '1.0') {
                $data[] = $this->getCurrency();
            }

            if ($protocolVersion == '2.0') {
                $data[] = str_pad($this->ott, 6);
            }

            return $data;
        }

        public function dateTime($dt, String $format = null, String $tz = null, Bool $isTimestamp = false) : Borica
        {
            if ($format != null) {
                $this->dt = Carbon::createFromFormat($format, $dt);
            }

            if ($this->dt == null) {
                $this->dt = Carbon::parse($dt);
            }

            if ($dt instanceof Carbon) {
                $this->dt = $dt;
            }

            if ($isTimestamp) {
                $this->dt = Carbon::createFromTimestamp($dt);
            }

            return $this;
        }

        public function getDateTime() : String
        {
            $dtFormat = 'YmdHis';

            if ($this->dt == null) {
                return Carbon::now()->format($dtFormat);
            }

            return $this->dt->format($dtFormat);
        }

        public function language(String $language) : Borica
        {
            $userLanguage = strtoupper($language);

            if (in_array($userLanguage, SUPPORTED_LANGUAGES))
            {
                $this->language = $userLanguage;
            }

            return $this;
        }

        public function getLanguage() : String
        {
            return $this->getLanguage();
        }

        public function currency(String $currency) : Borica
        {
            $currency = strtoupper($currency);

            if (in_array($currency, SUPPORTED_CURRENCIES))
            {
                $this->currency = $currency;
            }

            return $this;
        }

        public function getCurrency() : String
        {
            return $this->currency;
        }

        public function getTerminalID() : String
        {
            return $this->terminalID;
        }

        public function protocol(String $protocol) : String
        {
            if (in_array($protocol, PROTOCOL_VERSIONS))
            {
                $this->protocolVersion = $protocol;
            }

            return $this;
        }

        public function amount(float $amount) : Borica
        {
            if (!is_numeric($amount)) {
                throw new InvalidAmountException('');
            }

            $this->amount = str_pad($amount, 12 , "0", STR_PAD_LEFT);
            
            return $this;
        }

        public function order(array $order) : Borica
        {
            $this->orderID($order['id']);
            $this->orderDescription($order['description']);

            return $this;
        }

        public function orderID(String $id) : Borica
        {
            $length = strlen($id);

            if ($length > 1 and $length < 15) {
                $this->orderID = str_pad(substr($id, 0, 15), 15);
            }

            return $this;
        }
        
        public function orderDescription(String $description) : Borica
        {
            $this->orderDescription = str_pad(substr($description, 0, 125), 125);

            return $this;
        }

        public function ott(String $ott) : Borica
        {
            $this->ott = str_pad($ott, 6);

            return $this;
        }

        public function getGatewayEndpoint() : Bool
        {
            return (bool) ($this->isProduction) ? $this->gatewayUrl : $this->testGatewayUrl;
        }

        public function transactionCode(int $code) : Borica
        {
            if (in_array((String) $code, TRANSACTION_CODES)) {
                $this->transactionCode = $code;
            }

            return $this;
        }

        public function sign($message) : String
        {
            $signature = '';

            if (is_array($message)) {
                $message = implode('', $message);
            }

            $pKeyID = openssl_pkey_get_private($this->getPrivateKey(), $this->privateKeyPass);
            openssl_sign($message, $signature, $pKeyID);
            openssl_free_key($pKeyID);

            return $message . $signature;    
        }

        protected function getPrivateKey() : String
        {
            $fp = fopen($this->privateKey, 'r');
            $pk = fread($fp, 8192);
            fclose($fp);

            return $pk;
        }

        public function payment(String $type)
        {

        }
    }