<?php

/**
 * Класс для работы с SMS-сообщениями
 */
class SMS
{
    private $smsUrl = '';
    private $login = '';
    private $password = '';
    private $sender = '';

    private $ERRORS = [];

    public $debugMode = false;
    public $smsOn = 0;
    public $smsID = '';
    public $langID = 1;
    public $contentType = 'json';
    public $defaultSender = '';
    public $authorizationType = 'basic';


    public function __construct(int $langID = 1)
    {
    //	addlog(print_r($langID, true), false, DEBUG_LOG_FILE);

        $this->langID = $langID;
        $this->smsOn = getVal('SITE_SMS_ON', $langID);
        $this->smsUrl = getVal('SITE_SMS_URL');
        $this->login = getVal('SITE_SMS_LOGIN');
        $this->password = getVal('SITE_SMS_PASSWORD');
        $this->sender = getVal('SITE_SMS_SENDER');
    }


    /**
     * Отправляем sms с указанным текстом на телефон абонента
     * @return array|object Результат отправки
     */
    public function smsSend(string $phone = '', string $smsText = '', string $sendDateTime = '')
    {
        if (!$this->isValidSMSData($phone, $smsText)) {
            addlog(print_r($this->ERRORS, true), false, DEBUG_LOG_FILE);

            return false;
        }

        $postHeader = $this->getCurlPostHeader();
        $postFields = $this->getCurlPostFields($phone, $smsText, $sendDateTime);

        $REQUEST = curl_init();

        curl_setopt_array($REQUEST, [
            CURLOPT_URL             => $this->smsUrl,
            CURLOPT_POST            => TRUE,
            CURLOPT_HEADER          => TRUE,
            CURLOPT_RETURNTRANSFER  => TRUE,
            CURLOPT_POSTFIELDS      => $postFields,
            CURLOPT_HTTPHEADER      => $postHeader,
            CURLOPT_CONNECTTIMEOUT  => 120,
            CURLOPT_TIMEOUT         => 120,
        ]);

        $result = curl_exec($REQUEST);

        if (!curl_errno($REQUEST) && $this->debugMode) {
            $errors = curl_getinfo($REQUEST);

            if ($this->debugMode) {
                addlog(print_r($errors, true), false, DEBUG_LOG_FILE);
            }
        } else {
            addlog($smsText."\n\n", false, DEBUG_LOG_FILE);
        }

        curl_close($REQUEST);

        return !empty($result) ? json_decode($result, true) : [];
    }


    /**
     * Проверка данных для отправки SMS
     */
    private function isValidSMSData(string $phone = '', string $smsText = ''): bool
    {
        if (empty($this->smsOn)) {
            $this->smsError('MSG_SMS_OFF');
            return false;
        }

        if (empty($phone)) {
            $this->smsError('MSG_PHONE_UNDEFINED');
            return false;
        }

        if (empty($smsText)) {
            $this->smsError('MSG_SMS_TEXT_UNDEFINED');
            return false;
        }

        if (empty($this->login)) {
            $this->smsError('MSG_SMS_LOGIN_UNDEFINED');
            return false;
        }

        if (empty($this->password)) {
            $this->smsError('MSG_SMS_PASSWORD_UNDEFINED');
            return false;
        }

        if (empty($this->smsUrl)) {
            $this->smsError('MSG_SMS_URL_UNDEFINED');
            return false;
        }

        if (empty($this->contentType)) {
            $this->smsError('MSG_SMS_CONTENT_TYPE_UNDEFINED');
            return false;
        }

        return true;
    }


    /**
     * Получаем заголовки запроса
     */
    private function getCurlPostHeader(): array
    {
        $authorization = '';

        if ($this->contentType == 'xml') {
            $contentType = 'text/xml; charset=utf-8';
        } elseif ($this->contentType == 'json') {
            $contentType = 'application/json';
        } else {
            $this->smsError('MSG_SMS_INVALID_CONTENT_TYPE');
            return false;
        }

        if ($this->authorizationType == 'basic') {
            $authorization = $this->getBasicAuthorization();
        }

        return [
        //    'POST /api HTTP/1.1',
        //    'Host: '.$this->smsUrl,
            'Content-Type: '.$contentType,
            $authorization
        ];
    }


    /**
     * Получаем базовую авторизацию (аутентификацию)
     *
     * При базовой аутентификации клиент вместе с запросом отправляет серверу логин и пароль.
     * Эти данные отправляются в заголовке запроса Authorization в виде base64 кода.
     * Пример: "Authorization: Basic base64_encode(login:password)"
     */
    private function getBasicAuthorization(): string
    {
        if (empty($this->login)) {
            $this->smsError('MSG_SMS_LOGIN_UNDEFINED');
            return '';
        }

        if (empty($this->password)) {
            $this->smsError('MSG_SMS_PASSWORD_UNDEFINED');
            return '';
        }

        return 'Authorization: Basic '.base64_encode($this->login.':'.$this->password);
    }


    /**
     * Получаем тело запроса
     */
    private function getCurlPostFields(string $phone, string $smsText, string $sendDateTime = ''): string
    {
        $phone = preg_replace('/[^0-9]+/', '', $phone);
        $sendDate = !empty($sendDateTime) ? 'date_beg="'.$sendDateTime.'"' : '';

        if ($this->contentType == 'json') {
            return '{
                "messages": [
                    {
                        "recipient":"'.$phone.'",
                        "message-id":"'.$this->smsID.'",
                        "sms": {
                            "originator": "'.$this->sender.'",
                            "content": {
                                "text": "'.$smsText.'"
                            }
                        }
                    }
                ]
            }';
        } elseif ($this->contentType == 'xml') {
            return '<?xml version="1.0" encoding="utf-8" ?>
                <package login="'.$this->login.'" password="'.$this->password.'">
                    <message>
                        <default sender="'.$this->defaultSender.'"/>
                        <msg id="1" recipient="'.$phone.'" '.$sendDate.' sender="'.$this->sender.'">'.$smsText.'</msg>
                    </message>
                </package>';
        }

        $this->smsError('MSG_SMS_INVALID_CONTENT_TYPE');
        return false;
    }


    /**
     * Регистрируем ошибку
     */
	private function smsError(string $error_code)
    {
		$this->ERRORS[$error_code] = getVal($error_code, $this->langID);
	}


    /**
     * Получаем массив с ошибками
     */
    public function getSmsErrors()
    {
        return $this->ERRORS;
    }
}
