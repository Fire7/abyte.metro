<?
if ($arParams['USE_FILTER'] == 'Y') { 
    global ${$arParams["FILTER_NAME"]};
    $arrFilter = ${$arParams["FILTER_NAME"]};
    $arrFilter = array_merge($arrFilter, $arResult['ARR_FILTER']);
}
?>