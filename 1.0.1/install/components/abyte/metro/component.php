<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */

if(!CModule::IncludeModule("abyte.metro")){
    $this->AbortResultCache();
    ShowError(GetMessage('METRO_MODULE_NOT_INSTALLED'));
    return;
}

$arResult = array();

CJSCore::Init(array('jquery', 'jmetro', 'select2'));

if (isset($GLOBALS['ABYTE_METRO_ID']))
    $ID = ++$GLOBALS['ABYTE_METRO_ID'];
else
    $ID = $GLOBALS['ABYTE_METRO_ID'] = 0;

$arResult['ID'] = $ID;


$APPLICATION->AddHeadString('<style type="text/css"  >
#abyte_metro_' . $ID . '  .metro__elem.jmetro .jm_station.selected>.jm_station__label {
    background-image: url("'.$arParams['ICON'].'");
}
</style>
',true);

if ($arParams['WIDTH'] != 1000 && $arParams['WIDTH'] > 0) {
    $arResult['SIZE_K'] =  $arParams['WIDTH'] / 1000;

    switch ($arParams['CITY']) {
        case 'MSK':
            $fontSize = 16;
            $iconSize = 20;
            $iconLeft = -20;
            $iconTop = -5;
            break;
        case 'SPB':
            $fontSize = 18;
            $iconSize = 16;
            $iconLeft = -16;
            $iconTop = 0;
            break;
        default: break;
    }

    $APPLICATION->AddHeadString('<style type="text/css"  >
    #abyte_metro_' . $ID . ' .metro__elem.jmetro .jm_station .jm_station__name ,
    #abyte_metro_' . $ID . ' .metro__elem.jmetro .jm_station .jm_clone {
        font-size: '.$fontSize*$arResult['SIZE_K'].'px;
    }
    
      #abyte_metro_' . $ID . ' .metro__elem.jmetro .jm_station .jm_station__label {
        width: '.$iconSize*$arResult['SIZE_K'].'px;
        height: '.$iconSize*$arResult['SIZE_K'].'px;
        left: '.$iconLeft*$arResult['SIZE_K'].'px;
        top: '.$iconTop*$arResult['SIZE_K'].'px;
        
    }
    
    </style>
    ', true);
}


if ($arParams['USE_FILTER'] == 'Y') {

    $arFilterStations = array();

    if(empty($arParams["FILTER_NAME"]) || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"]))
    {
        $arrFilter = array();
    }
    else
    {
        global ${$arParams["FILTER_NAME"]};
        $arrFilter = ${$arParams["FILTER_NAME"]};
        if(!is_array($arrFilter))
            $arrFilter = array();
    }

    if ($_REQUEST[$arParams['FILTER_FIELD']]) {
        $arFilterStations = $_REQUEST[$arParams['FILTER_FIELD']];
    }
}



if($this->StartResultCache(false, array(($arParams['USE_FILTER'] == 'Y' ? implode(",", $arFilterStations) : false), ($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups())))){

if(!CModule::IncludeModule("iblock")){
    $this->AbortResultCache();
    ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
    return;
}


$arResult['SRC'] = CAbyteMetro::GetCitySrc($arParams['CITY']);
$arResult['SELECTED'] = is_array($arParams['SELECTED']) ? $arParams['SELECTED'] : array();


//FILTER;

if ($arParams['USE_FILTER'] == 'Y' && $arFilterStations) {

    $FILTER_ELEMENTS_ID = array();
    $arResult['ARR_FILTER'] = array();

// get metro iblock id;
    $arFilter = array(
        'TYPE' => 'abyte_metro',
        'CODE' => 'ab_metro_src'
    );

    $res = CIBlock::GetList(Array(), $arFilter);
    while ($ar_res = $res->Fetch()) {
        $metroIblockId = $ar_res['ID'];
    }

    if (!$metroIblockId > 0) {
        $this->AbortResultCache();
        ShowError(GetMessage("METRO_IBLOCK_NOT_FOUND"));
    }


    $arFilter = array(
        'IBLOCK_ID' => $metroIblockId,
        'XML_ID' => $arFilterStations
    );


    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, array('ID','XML_ID'));
    while ($ob = $res->GetNextElement()) {
        $arFields =  $ob->GetFields();

        $id =  $arFields ['ID'];
        $stationID =  $arFields['XML_ID'];

        if (!in_array($stationID, $arResult['SELECTED']))
        {
            $arResult['SELECTED'][] = $stationID;
        }

        $FILTER_ELEMENTS_ID[] = $id;
    }

    $arResult['ARR_FILTER']['PROPERTY'] = array();
    $arResult['ARR_FILTER']['PROPERTY'][$arParams['FILTER_FIELD']] = $FILTER_ELEMENTS_ID;
}

//INFO

if ($arParams['SHOW_INFO'] == 'Y') {

    //TODO: check iblock, property
    $arMetroInfo = array();



    $arFilter = Array(
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'ACTIVE' => 'Y',
        'ACTIVE_DATE' => 'Y'  ,
        '>'.$arParams['METRO_PROPERTY'].'.ID' => 0
    );


    $sections = array();

    if (is_array($arParams['SECTION']))
    foreach ($arParams['SECTION'] as $k => $sec_id) {
        if ($sec_id > -1) {
            $sections[] = $sec_id;
        }
    }

    if (count($sections) > 0)
        $arFilter['SECTION_ID'] = $sections;

    $arSelect = Array(
        'ID',
        'IBLOCK_ID',
        'NAME',
        $arParams['METRO_PROPERTY'],
        $arParams['METRO_PROPERTY'].'.XML_ID'
    );



    if (isset($arParams['PREVIEW_FIELD']) && $arParams['PREVIEW_FIELD'] )
        $arSelect[] = $arParams['PREVIEW_FIELD'];

    if (isset($arParams['DETAIL_FIELD'])  && $arParams['DETAIL_FIELD'])
        $arSelect[] = $arParams['DETAIL_FIELD'];

    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect );
    while($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();

        $stationID = $arFields[$arParams['METRO_PROPERTY'].'_XML_ID'];
        
        if (!isset($arFields[$arParams['PREVIEW_FIELD']]))
            $title = $arFields[$arParams['PREVIEW_FIELD'].'_VALUE'] ? $arFields[$arParams['PREVIEW_FIELD'].'_VALUE'] : '';
        else
            $title = $arFields[$arParams['PREVIEW_FIELD']] ? $arFields[$arParams['PREVIEW_FIELD']] : '';

        if (!isset($arFields[$arParams['DETAIL_FIELD']]))
            $detail = $arFields[$arParams['DETAIL_FIELD'].'_VALUE'] ? $arFields[$arParams['DETAIL_FIELD'].'_VALUE'] : '';
        else
            $detail = $arFields[$arParams['DETAIL_FIELD']] ? $arFields[$arParams['DETAIL_FIELD']] : '';


        if (!isset($arMetroInfo[$stationID]))
            $arMetroInfo[$stationID] = Array(
                'title' => $title,
                'detail' => ''
            );
        $arMetroInfo[$stationID]['detail'].=$detail.'<br />';

        if ($arParams['SELECT_INFO_STATIONS'] == 'Y' && !in_array($stationID, $arResult['SELECTED']))
            $arResult['SELECTED'][] = $stationID;
    }
    $arResult['INFO'] = $arMetroInfo;
}

$selected = array();
foreach ($arResult['SELECTED'] as $k => $id) {
    if ($id > 0)
        $selected[] = $id;
}
$arResult['SELECTED'] = $selected;

$this->IncludeComponentTemplate($componentPage);

}
// end StartResultCache
?>
