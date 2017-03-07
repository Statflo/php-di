# PHP DI Container

## installation

#### composer.json
```json

{
    "require": {
        /* ... */
        "statflo/php-di": "dev-master"
    },
    "repositories": [
        /* ... */
        {
            "type": "vcs",
            "no-api": true,
            "url":  "git@github.com:statflo/php-di.git"
        }
    ]
}
```

## Usage

#### config.xml

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<container xmlns="http://symfony.com/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/dic/services
        http://symfony.com/schema/dic/services/services-1.0.xsd">

    <services>
        <service id="statflo.service.whatever" class="Statflo\Service\Whatever" lazy="true">
            <argument type="service" id="statflo.amqp.connection" />
            <argument>%statflo.docker_env_variable%</argument>
            <argument>your string</argument>
        </service>
    </services>
</container>
```

#### bootstrap.php

```php
<?php

use Statflo\DI\Bootstrap;

$bootstrap = Bootstrap::run([
    'config_path' => dirname(__FILE__) . "/config",
    'parameters'  => [
        'statflo.docker_env_variable' => getenv('ENV_VAR') ?: 'fallback',
    ]
]);

$bootstrap
    ->get('statflo.service.whatever')
    ->execute()
;

```
