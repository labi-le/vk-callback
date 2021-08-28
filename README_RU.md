# vk-callback

[![GitHub license](https://img.shields.io/badge/license-BSD-green.svg)](https://github.com/labi-le/vk-callback/blob/main/LICENSE)
[![Packagist Stars](https://img.shields.io/packagist/stars/labile/vk-callback)](https://packagist.org/packages/labile/vk-callback/stats)
[![Packagist Stats](https://img.shields.io/packagist/dt/labile/vk-callback)](https://packagist.org/packages/labile/vk-callback/stats)

## Установка

`composer require labile/vk-callback`

### Реализация CallBack VK на php

```php
<?php

declare(strict_types=1);

use Astaroth\CallBack\Callback;


$callback = new Callback('5f6441e6', 'gyucjrdsyxtkkGRHNTyzauski');
$callback->listen(function ($data) {
    //любое действие, прям любое
});
```

