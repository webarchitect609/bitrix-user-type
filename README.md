Набор собственных пользовательских типов свойств, а также базовый функционал, призванный максимально упростить и 
ускорить разработку новых собственных пользовательских типов свойств.  

**Пока нестабильная версия - будьте внимательны!**


Если вы хотите [создавать свои типы свойств элемента инфоблока, то вам нужен пакет webarchitect609/bitrix-iblock-property-type](https://packagist.org/packages/webarchitect609/bitrix-iblock-property-type)


Как использовать: 
-----------------

1 Установить через composer 

`composer require webarchitect609/bitrix-user-type`

2 В init.php инициализировать используемые типы свойств. Например, 

`\WebArch\BitrixUserPropertyType\IblockSectionLinkType::init();`

3 Теперь можно создавать новые пользовательские поля, выбрав свойство нового типа!

Как разработать свой тип свойства: 
----------------------------------

1 Наследовать свой тип от базовой реализации `\WebArch\BitrixUserPropertyType\Abstraction\UserTypeBase`,

2 Определить обязательный статический метод `getDBColumnType()`, используя один из подходящих трейтов 
`\WebArch\BitrixUserPropertyType\Abstraction\*DbColTypeTrait`, чтобы не изобретать одно и то же.

3 Определить другие обязательные методы, такие как `getBaseType()`, `getDescription()` и т.д., которые требует 
`\WebArch\BitrixUserPropertyType\Abstraction\UserTypeInterface`

5 Инициализировать свой тип свойства в init.php

`MyUserType::init();`

6 Теперь можно создавать новые пользовательские поля, выбрав свойство нового типа!
