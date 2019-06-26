<?php

namespace WebArch\BitrixUserPropertyType;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Sale\Location\Admin\LocationHelper;
use WebArch\BitrixUserPropertyType\Abstraction\UserTypeBase;
use WebArch\BitrixUserPropertyType\Abstraction\UserTypeInterface;

/**
 * Class LocationType
 *
 * @package WebArch\BitrixUserPropertyType
 */
class LocationType extends UserTypeBase implements UserTypeInterface
{
    const USER_TYPE = 'sale_location';

    /**
     * @throws LoaderException
     */
    public static function init()
    {
        if (Loader::includeModule('sale')) {
            parent::init();
        }
    }

    /**
     * @return string
     */
    public static function getBaseType()
    {
        return self::BASE_TYPE_STRING;
    }

    /**
     * @return string
     */
    public static function getDescription()
    {
        return 'Привязка к местоположению (sale)';
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     * @return string
     */
    public static function getUserTypeId()
    {
        return self::USER_TYPE;
    }

    /**
     * Return internal type for storing url_preview user type values
     *
     * @param array $userField Array containing parameters of the user field.
     *
     * @return string
     */
    public static function getDBColumnType($userField)
    {
        global $DB;
        switch (strtolower($DB->type)) {
            case 'oracle':
                return 'varchar(20 char)';
            case 'mssql':
                return 'varchar(20)';
            case 'mysql':
            default:
                return 'varchar(20)';
        }
    }

    /**
     * @param array $userField
     *
     * @return array
     */
    public static function prepareSettings($userField): array
    {
        return [
            'DEFAULT_VALUE' => (int)$userField['SETTINGS']['DEFAULT_VALUE']
                               > 0 ? (int)$userField['SETTINGS']['DEFAULT_VALUE'] : '',
        ];
    }

    /**
     * @param array $userField Array containing parameters of the user field.
     * @param       $htmlControl
     * @param       $varsFromForm
     *
     * @return string
     */
    public static function getSettingsHTML($userField, $htmlControl, $varsFromForm): string
    {
        $result = '';
        $value = '';
        if ($varsFromForm) {
            $value = $GLOBALS[$htmlControl['NAME']]['DEFAULT_VALUE'];
        } elseif (\is_array($userField)) {
            $value = $userField['SETTINGS']['DEFAULT_VALUE'];
        }

        ob_start();
        global $APPLICATION;
        $APPLICATION->IncludeComponent(
            'bitrix:sale.location.selector.search',
            '',
            [
                'CACHE_TIME'                 => '36000000',
                'CACHE_TYPE'                 => 'N',
                'CODE'                       => $value,
                'ID'                         => '',
                'INITIALIZE_BY_GLOBAL_EVENT' => '',
                'INPUT_NAME'                 => $htmlControl['NAME'],
                'JS_CALLBACK'                => '',
                'PROVIDE_LINK_BY'            => 'code',
                'SUPPRESS_ERRORS'            => 'N',
            ],
            false,
            ['HIDE_ICONS' => 'Y']
        );

        $return = ob_get_clean();
        $result .= '
		<tr>
			<td>Значение по умолчанию:</td>
			<td>
				' . $return . '
			</td>
		</tr>
		';

        return $result;
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     */
    public static function getFilterHTML($userField, $htmlControl): string
    {
        $replacedName = str_replace(
            [
                '[',
                ']',
            ],
            '_',
            $htmlControl['NAME']
        );

        /** @var \CMain $APPLICATION */
        global $APPLICATION;
        ob_start();
        $APPLICATION->IncludeComponent(
            'bitrix:sale.location.selector.search',
            '',
            [
                'CACHE_TIME'                 => '36000000',
                'CACHE_TYPE'                 => 'N',
                'CODE'                       => $htmlControl['VALUE'],
                'ID'                         => '',
                'INITIALIZE_BY_GLOBAL_EVENT' => '',
                'INPUT_NAME'                 => $htmlControl['NAME'],
                'JS_CALLBACK'                => '',
                'JS_CONTROL_GLOBAL_ID'       => 'locationSelectors_' . $replacedName,
                'PROVIDE_LINK_BY'            => 'code',
                'SUPPRESS_ERRORS'            => 'N',
            ],
            false,
            ['HIDE_ICONS' => 'Y']
        );

        return ob_get_clean();
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     * @throws LoaderException
     */
    public static function getAdminListEditHTML($userField, $htmlControl): string
    {
        return static::getEditFormHTML($userField, $htmlControl);
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     * @throws LoaderException
     */
    public static function getEditFormHTML($userField, $htmlControl): string
    {
        $return = '&nbsp;';
        $replacedName = str_replace(
            [
                '[',
                ']',
            ],
            '_',
            $htmlControl['NAME']
        );

        if ($userField['EDIT_IN_LIST'] === 'Y') {
            if ($userField['ENTITY_VALUE_ID'] < 1 && !empty($userField['SETTINGS']['DEFAULT_VALUE'])) {
                $htmlControl['VALUE'] = $userField['SETTINGS']['DEFAULT_VALUE'];
            }
            /** @var \CMain $APPLICATION */
            global $APPLICATION;
            ob_start();
            $deferredControlName = 'defered_' . $replacedName;
            $globalControlName = 'locationSelectors_' . $replacedName;
            $APPLICATION->IncludeComponent(
                'bitrix:sale.location.selector.search',
                '',
                [
                    'CACHE_TIME'                 => '36000000',
                    'CACHE_TYPE'                 => 'N',
                    'CODE'                       => $htmlControl['VALUE'],
                    'ID'                         => '',
                    'INITIALIZE_BY_GLOBAL_EVENT' => '',
                    'INPUT_NAME'                 => $htmlControl['NAME'],
                    'JS_CALLBACK'                => '',
                    'JS_CONTROL_GLOBAL_ID'       => $globalControlName,
                    'JS_CONTROL_DEFERRED_INIT'   => $deferredControlName,
                    'PROVIDE_LINK_BY'            => 'code',
                    'SUPPRESS_ERRORS'            => 'N',
                ],
                false,
                ['HIDE_ICONS' => 'Y']
            ); ?>
            <script>
                if (!window.BX && top.BX) {
                    window.BX = top.BX;
                }
                BX.loadScript("/bitrix/components/bitrix/sale.location.selector.search/templates/.default/script.js", function () {
                    BX.ready(function () {
                        BX.locationsDeferred["<?=$deferredControlName?>"]();
                    });
                });
            </script>
            <?php $return = '<div class="location_type_prop_html">' . ob_get_clean() . '</div>';
        } elseif (!empty($htmlControl['VALUE'])) {
            $return = static::getAdminListViewHTML($userField, $htmlControl);
        }

        return $return;
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     * @throws LoaderException
     */
    public static function getAdminListViewHTML($userField, $htmlControl): string
    {
        if (!empty($htmlControl['VALUE'])) {
            Loader::includeModule('sale');

            return '[' . $htmlControl['VALUE'] . ']' . LocationHelper::getLocationStringByCode($htmlControl['VALUE']);
        }

        return '&nbsp;';
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     *
     * @throws LoaderException
     */
    public static function getAdminListEditHTMLMulty($userField, $htmlControl): string
    {
        return static::getEditFormHTMLMulty($userField, $htmlControl);
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     *
     * @throws LoaderException
     */
    public static function getEditFormHTMLMulty($userField, $htmlControl): string
    {
        $return = '&nbsp;';
        if ($userField['EDIT_IN_LIST'] === 'Y') {
            $replacedName = str_replace(
                [
                    '[',
                    ']',
                ],
                '_',
                $htmlControl['NAME']
            );

            Loader::includeModule('sale');
            global $APPLICATION;

            ob_start();

            $deferredControlName = 'defered_' . $replacedName;
            $tmpInputName = $replacedName . '_TMP';
            $APPLICATION->IncludeComponent(
                'adv:sale.location.selector.system',
                '',
                [
                    'CACHE_TYPE'               => 'N',
                    'CACHE_TIME'               => '0',
                    'INPUT_NAME'               => $tmpInputName,
                    'SELECTED_IN_REQUEST'      => ['L' => $htmlControl['VALUE']],
                    'PROP_LOCATION'            => 'Y',
                    'JS_CONTROL_DEFERRED_INIT' => $deferredControlName,
                    'JS_CONTROL_GLOBAL_ID'     => 'locationSelectors_' . $replacedName,
                ],
                false
            );

            $resultComponent = ob_get_clean();
            $result =
                '<div class="location_type_prop_multi_html" data-realInputName="' . $htmlControl['NAME'] . '">
			<script type="text/javascript" data-skip-moving="true">
                if (!window.BX && top.BX) {
                    window.BX = top.BX;
                }
               
			    if(typeof window["LoadedLocationMultyScripts"] !== "boolean" || (typeof window["LoadedLocationMultyScripts"] === "boolean" && !window["LoadedLocationMultyScripts"])){
			        window["LoadedLocationMultyScripts"] = true;
                    var bxInputdeliveryLocMultiStep3 = function()
                    {
                        BX.loadScript("/local/templates/.default/components/bitrix/system.field.edit/sale_location/_script.js", function(){
                            window["LoadedLocationMultyScriptMain"] = true;
                            BX.ready(function() {
                                BX.onCustomEvent("deliveryGetRestrictionHtmlScriptsReady");
                                BX.locationsDeferred["' . $deferredControlName . '"]();
                                initPropLocationRealVals("' . $tmpInputName . '", "' . $htmlControl['NAME'] . '");
                            });
                        });
                    };
        
                    var bxInputdeliveryLocMultiStep2 = function()
                    {
                        BX.load([
                            "/bitrix/js/sale/core_ui_etc.js",
                            "/bitrix/js/sale/core_ui_autocomplete.js",
                            "/bitrix/js/sale/core_ui_itemtree.js"
                            ],
                            bxInputdeliveryLocMultiStep3
                        );
                    };
        
                    BX.loadScript("/bitrix/js/sale/core_ui_widget.js", bxInputdeliveryLocMultiStep2);
				}
				else{
			        if(typeof window["LoadedLocationMultyScriptMain"] !== "boolean" || (typeof window["LoadedLocationMultyScriptMain"] === "boolean" && !window["LoadedLocationMultyScriptMain"])){
			            BX.loadScript("/local/templates/.default/components/bitrix/system.field.edit/sale_location/_script.js", function(){
			                BX.ready(function() {
                                BX.onCustomEvent("deliveryGetRestrictionHtmlScriptsReady");
                                BX.locationsDeferred["' . $deferredControlName . '"]();
                                initPropLocationRealVals("' . $tmpInputName . '", "' . $htmlControl['NAME'] . '");
                            });
			            });
			        }
			        else{
			            BX.ready(function() {
                            BX.onCustomEvent("deliveryGetRestrictionHtmlScriptsReady");
                            BX.locationsDeferred["' . $deferredControlName . '"]();
                            initPropLocationRealVals("' . $tmpInputName . '", "' . $htmlControl['NAME'] . '");
                        });
			        }
				}
				if(typeof initPropLocationRealVals !== "function"){
                    function initPropLocationRealVals(name, realName){
                        var el = document.querySelector( "input[name=\'"+name+"[L]\']" );
                        if(!el || typeof el === "undefined"){
                            el = top.document.querySelector( "input[name=\'"+name+"[L]\']" );
                        }
                        if(!!el) {
                            setPropLocationRealVals(el, realName);
                        }
                    }
                }
                if(typeof setPropLocationRealVals !== "function"){
                    function setPropLocationRealVals(el, realName){
                        if(!!el){
                            var firstVal = el.getAttribute("value");
                            if(firstVal.length > 0){
                                var items = firstVal.split(":");
                                var index, val;
                                var div = el.closest("div");
                                var delItems = div.querySelectorAll("input.real_inputs");
                                if(delItems.length>0){
                                    for(index in delItems){
                                        if(delItems.hasOwnProperty(index)){
                                            delItems[index].parentNode.removeChild(delItems[index]);
                                        }
                                    }
                                }
                                if(items.length > 0){
                                    for(index in items){
                                        if (items.hasOwnProperty(index)){
                                            val = items[index];
                                            if(val > 0){
                                                var newInput = document.createElement("input");
                                                newInput.setAttribute("name", realName);
                                                newInput.setAttribute("value", val);
                                                newInput.setAttribute("type", "hidden");
                                                newInput.className = "real_inputs";
                                                
                                                div.appendChild(newInput);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
			</script>
   
            <!--suppress HtmlUnknownTarget -->
			<link rel="stylesheet" type="text/css" href="/bitrix/panel/main/adminstyles_fixed.css">
			<!--suppress HtmlUnknownTarget -->
			<link rel="stylesheet" type="text/css" href="/bitrix/panel/main/admin.css">
			<!--suppress HtmlUnknownTarget -->
			<link rel="stylesheet" type="text/css" href="/bitrix/panel/main/admin-public.css">
			<!--suppress HtmlUnknownTarget -->
			<link rel="stylesheet" type="text/css" href="/local/templates/.default/components/bitrix/system.field.edit/sale_location/_style.css">
		' . $resultComponent . '</div>';

            $return = $result;
        } elseif (!empty($htmlControl['VALUE'])) {
            $return = static::getAdminListViewHTMLMulty($userField, $htmlControl);
        }

        return $return;
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     *
     * @throws LoaderException
     */
    public static function getAdminListViewHTMLMulty($userField, $htmlControl): string
    {
        if (!empty($htmlControl['VALUE'])) {
            Loader::includeModule('sale');
            $arPrint = [];
            if (\is_array($htmlControl['VALUE']) && !empty($htmlControl['VALUE'])) {
                foreach ($htmlControl['VALUE'] as $val) {
                    if (!empty($val) && (int)$val > 0) {
                        $arPrint[] = '[' . $val . ']' . LocationHelper::getLocationStringByCode($val);
                    }
                }
            }

            return implode(' / ', $arPrint);
        }

        return '&nbsp;';
    }

    /**
     * @param $userField
     *
     * @return string
     * @throws LoaderException
     */
    public static function onSearchIndex($userField): string
    {
        if (\is_array($userField['VALUE'])) {
            return static::getAdminListViewHTMLMulty($userField, ['VALUE' => $userField['VALUE']]);
        }

        return static::getAdminListViewHTML($userField, ['VALUE' => $userField['VALUE']]);
    }
}
