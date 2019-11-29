# Yii2 Netelip SMS api integration

This component allows yii2 apps to send SMS messages via Netelip API (requires account)

### Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist eseperio/yii2-netelip-sms:"~1.0.0"
```

or add

```json
eseperio/yii2-netelip-sms:"~1.0.0"
```

to the require section of your composer.json.


### Configuration
  Add to your components configuration.
```
  components => [
           'netelip'=>[
                   'class' => 'eseperio\netelipsms\NetelipSms',
                   'token' => 'yoursecuritytoken'
  ]
```
### Usage:
  Remember that all numbers must be written in international mode (prefixed with 00 and the country code.)

 ```
  Yii::$app->netelip->sms('0034000000', "Message payload");
 ```


#### Author

[Waizabú aplicaciones cloud](https://waizabu.com)
