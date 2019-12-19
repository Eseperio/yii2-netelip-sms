<?php
/**
 * Copyright (c) 2019.
 * Developed by Waizabú. V1.0 MIT licensed
 */

namespace eseperio\netelipsms;

use Yii;
use yii\helpers\StringHelper;

/**
 * Extension NetelipSms
 * Usage:
 * Add to your components configuration.
 * components => [
 *          'netelip'=>[
 *                  'class' => 'eseperio\netelipsms\NetelipSms',
 *                  'token' => 'yoursecuritytoken'
 * ]
 *
 * // Remember that all numbers must be written in international mode (prefixed with 00 and the country code.)
 * Yii::$app->netelip->sms('0034000000',"Message payload");
 */
class NetelipSms extends \yii\base\Component
{
    const REQUEST_SUCCESS = true;
    const REQUEST_ERROR = false;

    const RESPONSE_CODE_SUCCESS = 200;
    const RESPONSE_CODE_UNAUTHORIZED = 401;
    const RESPONSE_CODE_PAYMENT_REQUIRED = 402;
    const RESPONSE_CODE_PRECONDITION_FAILED = 412;
    const RESPONSE_CODE_PARAMETERS_ERROR = 103;
    const RESPONSE_CODE_REQUIRED_PARAMETERS_ERROR = 109;


    /**
     * User token.
     * @var string
     */
    public $token;

    /**
     * @var string The name of sender to be used. It will be truncated to 11 chars. If not
     * set then the application name will be used
     */
    public $from;

    /**
     * Api URL
     * Api Url to call without 'http'
     * @var string
     */
    public $url = 'https://apps.netelip.com/sms/api.php';

    /**
     * @var int how much long the sender name can be. Defaults to 11
     */
    public $maxNameLength = 11;
    /**
     * Server Response
     * @var array
     */
    protected $response;

    /**
     * Server Response CODE
     * @var array
     */
    protected $responseCode;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!function_exists('curl_init'))
            throw new \yii\base\InvalidArgumentException(__CLASS__ . " requires cURL to work");
        parent::init();
    }

    /**
     *
     * @param $phone
     * @param $message
     */
    public function sms($phone, $message)
    {
        if (empty($this->from))
            $this->from = Yii::$app->name;

        $status = $this->request([
            'destination' => $phone,
            'message' => Yii::$app->formatter->asText($message),
            'from' => StringHelper::truncate($this->from, $this->maxNameLength)
        ]);

        return $this->responseCode == self::RESPONSE_CODE_SUCCESS;

    }

    /**
     * API Call
     * @param string $call  name to call
     * @param array $params call parameteres
     * @return bool|string
     */
    protected function request(array $params = array())
    {
        $params = array_map(function ($val) {
            return join(',', (array)$val);
        }, $params);
        $params = array_merge($params, ['token' => $this->token]);

        $url = $this->url;
        $post = http_build_query($params, '', '&');
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        Yii::debug('calling: ' . $url . ':' . PHP_EOL . print_r($params, true), 'NetelipSMS');

        $response = curl_exec($ch);

        $this->responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        switch ($this->responseCode) {
            case self::RESPONSE_CODE_SUCCESS:
                Yii::info("Message sent succesfully");
                break;
            case self::RESPONSE_CODE_UNAUTHORIZED:
                Yii::warning("Unauthorized request");
                break;
            case self::RESPONSE_CODE_PAYMENT_REQUIRED:
                Yii::warning("Out of credit");
                break;
            case self::RESPONSE_CODE_PRECONDITION_FAILED:
                Yii::warning("Request malformed");
                break;
            case self::RESPONSE_CODE_PARAMETERS_ERROR:
                Yii::warning("Parameters error");
                break;
            case self::RESPONSE_CODE_REQUIRED_PARAMETERS_ERROR:
                Yii::warning("Required parameters missed");
                break;
        }

        curl_close($ch);

        return $this->response = simplexml_load_string($response);
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function getResponseCode(): array
    {
        return $this->responseCode;
    }


}
