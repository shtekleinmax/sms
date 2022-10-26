# Описание

**class.SMS.php** - класс для интеграции с сервисом отправки SMS (протестировано с Kazinfotech и Play mobile). На данный момент поддерживает отправку запросов в 2 форматах: XML или JSON.

## ToDo
   - [ ] Добавить нормальное логирование для отладки
   - [ ] Проработать возможность добавлять заголовки и тело запроса, с учётом SMS-сервиса
   - [ ] Добавить поддержку отправки SMS по HTTP и SOAP

## Расположение

/includes/

## Необходимые настройки в CMS

**SITE_SMS_ON** - (boolean) включить/отключить отправку SMS

**SITE_SMS_URL** - URL API для отправки запросов

**SITE_SMS_LOGIN** - Логин для авторизации в API

**SITE_SMS_PASSWORD** - Пароль для авторизации в API

**SITE_SMS_SENDER** - Имя/Номер отправителя

**SITE_SMS_SENDER** - Имя/Номер отправителя

## Сообщения об ошибках

**MSG_SMS_OFF** - Отправка SMS отключена

**MSG_PHONE_UNDEFINED** - Не передан телефон получателя

**MSG_SMS_TEXT_UNDEFINED** - Не передан текст сообщения

**MSG_SMS_LOGIN_UNDEFINED** - Не передан логин для авторизации в API

**MSG_SMS_PASSWORD_UNDEFINED** - Не передан пароль для авторизации в API

**MSG_SMS_URL_UNDEFINED** - Не передан URL API

**MSG_SMS_CONTENT_TYPE_UNDEFINED** - Не передан тип контента

**MSG_SMS_INVALID_CONTENT_TYPE** - Не корректный тип контента
