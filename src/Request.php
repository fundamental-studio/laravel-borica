<?php

namespace Fundamental\Borica;

use Carbon\Carbon;
use Fundamental\Borica\Exceptions\InvalidAmountException;
use Fundamental\Borica\Exceptions\UnsupportedLanguageException;
use Fundamental\Borica\Exceptions\UnsupportedCurrencyException;
use Fundamental\Borica\Exceptions\UnkownTransactionCodeException;
use Fundamental\Borica\Exceptions\InvalidProtocolVersionException;

class Request
{
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

    private $gatewayUrl = 'https://gate.borica.bg/boreps/';
    private $testGatewayUrl = 'https://gatet.borica.bg/boreps/';

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

    /**
     * Undocumented function
     *
     * @param [type] $language
     * @param [type] $currency
     * @param [type] $protocolVersion
     * @param [type] $ott
     */
    public function __construct($language = null, $currency = null, $protocolVersion = null, $ott = null)
    {
        $this->terminalID = config('terminalID');
        $this->isProduction = config('production');

        $this->privateKey = config('privateKey');
        $this->privateKeyPass = config('privateKeyPass');

        $this->protocolVersion = $this->protocol($protocolVersion);
        $this->ott = $this->ott($ott);

        $this->language = $this->language($language);
        $this->currency = $this->currency($currency);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function generateRequestArray()
    {
        $protocolVersion = $this->getProtocolVersion();

        $data = [
            'transactionCode'   => $this->getTransactionCode(),
            'dateTime'          => $this->getDateTime(),
            'amount'            => str_pad($this->getAmount() * 100, 12, "0", STR_PAD_LEFT),
            'terminalID'        => $this->getTerminalID(),
            'orderID'           => str_pad(substr($this->getOrderID(), 0, 15), 15),
            'orderDescription'  => str_pad(substr($this->getOrderDescription(), 0, 15), 15),
            'language'          => $this->getLanguage(),
            'protocolVersion'   => $protocolVersion
        ];

        if ($protocolVersion != '1.0') {
            $data['currency'] = $this->getCurrency();
        }

        if ($protocolVersion == '2.0') {
            $data['ott'] = str_pad($this->getOtt(), 6);
        }

        return $data;
    }

    /**
     * Undocumented function
     *
     * @param [type] $dt
     * @param String $format
     * @param String $tz
     * @param Bool $isTimestamp
     * @return Borica
     */
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

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getDateTime() : String
    {
        $dtFormat = 'YmdHis';

        if ($this->dt == null) {
            return Carbon::now()->format($dtFormat);
        }

        return $this->dt->format($dtFormat);
    }

    /**
     * Undocumented function
     *
     * @param String $language
     * @return Borica
     */
    public function language(String $language) : Borica
    {
        $userLanguage = strtoupper($language);

        if (in_array($userLanguage, $this::SUPPORTED_LANGUAGES))
        {
            $this->language = $userLanguage;
        }

        return $this;
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getLanguage() : String
    {
        return $this->language;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getOrderID() : String
    {
        return $this->orderID;
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getOrderDescription() : String
    {
        return $this->orderDescription;
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getProtocolVersion() : String
    {
        return $this->protocolVersion;
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getOtt() : String
    {
        return $this->ott;
    }

    /**
     * Undocumented function
     *
     * @param String $currency
     * @return Borica
     */
    public function currency(String $currency) : Borica
    {
        $currency = strtoupper($currency);

        if (in_array($currency, $this::SUPPORTED_CURRENCIES))
        {
            $this->currency = $currency;
        }

        return $this;
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getCurrency() : String
    {
        return $this->currency;
    }

    /**
     * Undocumented function
     *
     * @return String
     */
    public function getTerminalID() : String
    {
        return $this->terminalID;
    }

    /**
     * Undocumented function
     *
     * @param String $protocol
     * @return String
     */
    public function protocol(String $protocol) : String
    {
        if (in_array($protocol, $this::PROTOCOL_VERSIONS))
        {
            $this->protocolVersion = $protocol;
        }

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param float $amount
     * @return Borica
     */
    public function amount(float $amount) : Borica
    {
        if (!is_numeric($amount)) {
            throw new InvalidAmountException('');
        }

        $this->amount = str_pad($amount, 12 , "0", STR_PAD_LEFT);

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param array $order
     * @return Borica
     */
    public function order(array $order) : Borica
    {
        $this->orderID($order['id']);
        $this->orderDescription($order['description']);

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param String $id
     * @return Borica
     */
    public function orderID(String $id) : Borica
    {
        $length = strlen($id);

        if ($length > 1 and $length < 15) {
            $this->orderID = str_pad(substr($id, 0, 15), 15);
        }

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param String $description
     * @return Borica
     */
    public function orderDescription(String $description) : Borica
    {
        $this->orderDescription = str_pad(substr($description, 0, 125), 125);

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param String $ott
     * @return Borica
     */
    public function ott(String $ott) : Borica
    {
        $this->ott = str_pad($ott, 6);

        return $this;
    }

    /**
     * Undocumented function
     *
     * @return Bool
     */
    public function getGatewayEndpoint() : Bool
    {
        return (bool) ($this->isProduction) ? $this->gatewayUrl : $this->testGatewayUrl;
    }

    /**
     * Undocumented function
     *
     * @param integer $code
     * @return Borica
     */
    public function transactionCode(int $code) : Borica
    {
        if (in_array((String) $code, $this::TRANSACTION_CODES)) {
            $this->transactionCode = $code;
        }

        return $this;
    }

    public function getTransactionCode()
    {
        return $this->transactionCode;
    }

    /**
     * Undocumented function
     *
     * @param [type] $message
     * @return String
     */
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

    /**
     * Undocumented function
     *
     * @return String
     */
    protected function getPrivateKey() : String
    {
        $fp = fopen($this->privateKey, 'r');
        $pk = fread($fp, 8192);
        fclose($fp);

        return $pk;
    }

    /**
     * Undocumented function
     *
     * @param String $type
     * @return void
     */
    public function generate()
    {
        $message = '';
        $requestData = $this->generateRequestArray();

        foreach ($requestData as $key => $parameter)
        {
            $message .= $parameter;
        }

        return $this->sign($message);
    }
}