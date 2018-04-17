<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$IMG_SRC = '';
$className = '';


switch ($arParams['CITY']) {
    case 'MSK':
        $IMG_SRC = $templateFolder.'/images/msk.svg';
        $IMG_SRC_PNG = $templateFolder.'/images/msk.png';
        $className = 'msk';
        break;
    case 'SPB':
        $IMG_SRC = $templateFolder.'/images/spb.svg';
        $IMG_SRC_PNG = $templateFolder.'/images/spb.png';
        $className = 'spb';
        break;
    default: break;
}

$USE_FILTER = $arParams['USE_FILTER'] == 'Y';
?>



<div class="metro" id="abyte_metro_<?=$arResult['ID']?>">
    <div class="metro__elem"></div>
</div>

<script type="text/javascript">
    (function () {
        var metroElem = $('#abyte_metro_<?=$arResult['ID']?> .metro__elem');
        metroElem.jmetro({
            editable: <?=$USE_FILTER ? 'true' : 'false'?>,
            img: '<?=$IMG_SRC?>',
            error_img: '<?=$IMG_SRC_PNG?>',
            className: '<?=$className?>',
            stations_info: <?=is_array($arResult['INFO']) ? \Bitrix\Main\Web\Json::encode($arResult['INFO']) : '{}'?>,
            show_info: <?=$arParams['SHOW_ALL_LABELS'] == 'Y' ? 'true' : 'false'?>,
            width: '<?=$arParams['WIDTH']?>px',
            stations_src: <?=$arResult['SRC']?>,
            value: <?=is_array($arResult['SELECTED']) ? \Bitrix\Main\Web\Json::encode($arResult['SELECTED']) : '[]'?>,
            disabled: <?=is_array($arParams['DISABLED']) ? \Bitrix\Main\Web\Json::encode($arParams['DISABLED']) : '[]' ?>,
            clear_button: <?=$USE_FILTER && $arParams['SHOW_CLEAR'] == 'Y' ? 'true' : 'false'?>,
            delete_message: '<?=GetMessage('CLEAR_SCHEME')?>',
            search_and_input: <?=$USE_FILTER && $arParams['SHOW_CLEAR'] == 'Y' ? 'true' : 'false'?>,
            limit: <?=$arParams['LIMIT'] > 0 ? $arParams['LIMIT'] : 0?>,
            blur: <?=$arParams['BLUR_ON_SELECT'] == 'Y' ? 'true' : 'false'?>,


            show_submit: <?=$USE_FILTER && $arParams['SHOW_SUBMIT'] == 'Y' ? 'true': 'false' ?>,
            submit_param: '<?=$arParams['FILTER_FIELD']?>',
            submit_message: '<?=GetMessage('SEARCH')?>'
        });

        <?if($arParams['JS_ELEM'] !==''):?>
        var valueElem = $('<?=$arParams['JS_ELEM']?>');
        if (valueElem.length) {
            metroElem.on('change.jmetro', function (e) {
                valueElem.val(metroElem.val());
            });
            valueElem.val(metroElem.val());
        }
        <?endif;?>
    })();
</script>
