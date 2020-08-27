Change Log
============

0.7.1
-----

### Исправлено
- Поле типа `TimeType` не работало в админке из-за внутреннего бага Битрикс, не способного разрешить связку с
    `TimeField`

0.7.0
-----

### Добавлено
- Метод `TimeType::parseValue()` для разбора строки в отдельные компоненты времени.
- Метод `TimeType::createValueString()` для соединения отдельных компонентов времени в строку.
- Метод `TimeType::toString()` для возвращения строкового представления данных.
- Связка `TimeType` с типом поля `\WebArch\BitrixOrmTools\Field\TimeField` из
    [webarchitect609/bitrix-orm-tools](https://packagist.org/packages/webarchitect609/bitrix-orm-tools).

0.6.1
-----

### Добавлено
- Класс `WeekDayISO8601` с константами дней недели по ISO-8601. 

0.6.0
-----

Добавлен новый тип "Время(без даты)"

0.5.1
-----

Переход на использование `webarchitect609/bitrix-cache:1.4.4`

0.5.0
-----

В README.md добавлена известная проблема "Bitrix\Main\SystemException: Unknown field definition `UF_YOUR_TYPE`" при
работе с HL-блоками и способ её устранения.
