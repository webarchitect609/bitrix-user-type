Change Log
============

0.8.0
-----

### Добавлено

- поле `TimeField` перемещено из `webarchitect609/bitrix-orm-tools` и добавлен `class_alias` для сохранения обратной
  совместимости

0.7.6
-----

### Добавлено

- `php: ^7.2 || ^8.0`

0.7.5
-----

### Добавлено

- трейт `CanUseArrayValueForSingleFieldTrait` для ускорения разработки и уменьшения дублирования кода.

0.7.4
-----

### Исправлено

- не был реализован `UserTypeInterface::canUseArrayValueForSingleField()`, из-за чего `TimeType` и другие свойства были
  сломаны.

0.7.3
-----

### Исправлено

- метод `TimeType::toString()` не добавлял ведущие нули к часам, минутам и секундам.

0.7.2
-----

### Исправлено

- уточнена проверка на запрос из административной панели в `TimeType::getEntityField()`

0.7.1
-----

### Исправлено

- поле типа `TimeType` не работало в админке из-за внутреннего бага Битрикс, не способного разрешить связку с
  `TimeField`

0.7.0
-----

### Добавлено

- метод `TimeType::parseValue()` для разбора строки в отдельные компоненты времени.
- метод `TimeType::createValueString()` для соединения отдельных компонентов времени в строку.
- метод `TimeType::toString()` для возвращения строкового представления данных.
- связка `TimeType` с типом поля `\WebArch\BitrixOrmTools\Field\TimeField` из
  [webarchitect609/bitrix-orm-tools](https://packagist.org/packages/webarchitect609/bitrix-orm-tools).

0.6.1
-----

### Добавлено

- класс `WeekDayISO8601` с константами дней недели по ISO-8601.

0.6.0
-----

### Добавлено

- добавлен новый тип "Время(без даты)"

0.5.1
-----

Переход на использование `webarchitect609/bitrix-cache:1.4.4`

0.5.0
-----

В README.md добавлена известная проблема "Bitrix\Main\SystemException: Unknown field definition `UF_YOUR_TYPE`" при
работе с HL-блоками и способ её устранения.
