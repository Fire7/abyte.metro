<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
if(!CModule::IncludeModule('iblock')) return;
if(!CModule::IncludeModule('abyte.metro')) return;


if($arCurrentValues['IBLOCK_ID'] > 0) {
    $bWorkflowIncluded = CIBlock::GetArrayByID($arCurrentValues['IBLOCK_ID'], 'WORKFLOW') == 'Y' && CModule::IncludeModule('workflow');
}
else
    $bWorkflowIncluded = CModule::IncludeModule('workflow');

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock=array();
$rsIBlock = CIBlock::GetList(Array('sort' => 'asc'), Array('TYPE' => $arCurrentValues['IBLOCK_TYPE'], 'ACTIVE'=>'Y'));
while($arr=$rsIBlock->Fetch())
{
    $arIBlock[$arr['ID']] = '['.$arr['ID'].'] '.$arr['NAME'];
}

$arFilterIBlock = array();
$rsIBlock = CIBlock::GetList(Array('sort' => 'asc'), Array('TYPE' => $arCurrentValues['FILTER_IBLOCK_TYPE'], 'ACTIVE'=>'Y'));
while($arr=$rsIBlock->Fetch())
{
    $arFilterIBlock[$arr['ID']] = '['.$arr['ID'].'] '.$arr['NAME'];
}






$arComponentParameters = array(
    'GROUPS' => array(
        'FILTER' => array(
            'NAME' => GetMessage('FILTER_SETTINGS'),
            'SORT' => '190'
        )
    ),
    'PARAMETERS' => array(

        'CITY' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('CITY'),
            'TYPE' => 'LIST',
            'MULTIPLE' => 'N',
            'VALUES' => array (
                'MSK' => GetMessage('MSK'),
                'SPB' => GetMessage('SPB')              
            ),
            'DEFAULT' => 'MSK',
            'REFRESH' => 'Y',
        ),

        'SHOW_INFO' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('SHOW_INFO'),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
            'REFRESH' => 'Y'
        ),
        
        'USE_FILTER' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('USE_FILTER'),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'N',
            'REFRESH' => 'Y'
        ),

        'WIDTH' => array(
            'PARENT' => 'VISUAL',
            'NAME' => GetMessage('WIDTH'),
            'TYPE' => 'STRING',
            'DEFAULT' => '1000'
        ),


        'BLUR_ON_SELECT' => array(
            'PARENT' => 'VISUAL',
            'NAME' => GetMessage('BLUR_ON_SELECT'),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y'
        ),


        'CACHE_TIME'  =>  Array('DEFAULT'=>86400*10),
        'CACHE_GROUPS' => array(
            'PARENT' => 'CACHE_SETTINGS',
            'NAME' => GetMessage('CACHE_GROUPS'),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        )
	)    
);

if (!$arCurrentValues['CITY'])
    $arCurrentValues['CITY'] = 'MSK';

$metroSrc = json_decode(CAbyteMetro::GetCitySrc($arCurrentValues['CITY']));
$arMetro = array();

foreach ($metroSrc as $k => $obj) {
    $arMetro[$obj->id] = $obj->name;
}

$arComponentParameters['PARAMETERS']['SELECTED'] = array(
    'NAME' => GetMessage('SELECTED'),
    'TYPE' => 'LIST',
    'MULTIPLE' => 'Y',
    'SIZE' => 10,
    'VALUES' => $arMetro,
    'DEFAULT' => ''
);

$arComponentParameters['PARAMETERS']['DISABLED'] = array(
    'NAME' => GetMessage('DISABLED'),
    'TYPE' => 'LIST',
    'MULTIPLE' => 'Y',
    'SIZE' => 10,
    'VALUES' => $arMetro,
    'DEFAULT' => $arCurrentValues['CITY'] == 'SPB' ? Array(
        2005,
        2011,
        2017,
        2036,
        2055,
        2063,
        2071
    ) : ''
);

$arComponentParameters['PARAMETERS']['ICON'] = array(
    'PARENT' => 'VISUAL',
    'NAME' => GetMessage('ICON'),
    'TYPE' => 'FILE',
    'FD_TARGET' => 'F',
    'FD_EXT' => 'svg,jpg,jpeg,png,gif,ico',
    'FD_UPLOAD' => true,
    'FD_USE_MEDIALIB' => true,
    'FD_MEDIALIB_TYPES' => Array('image'),
    'DEFAULT' => '/bitrix/components/abyte/metro/templates/.default/images/check_'.($arCurrentValues['CITY'] ? strtolower($arCurrentValues['CITY']) : 'msk').'.svg'
);



if ($arCurrentValues['USE_FILTER'] == 'Y') {

   $arComponentParameters['PARAMETERS']['FILTER_NAME'] = array(
        'PARENT' => 'FILTER',
        'NAME' => GetMessage('IBLOCK_FILTER_NAME_OUT'),
        'TYPE' => 'STRING',
        'DEFAULT' => 'arrFilter',
    );

    $arComponentParameters['PARAMETERS']['FILTER_IBLOCK_TYPE'] = array(
        'PARENT' => 'FILTER',
        'NAME' => GetMessage('IBLOCK_TYPE'),
        'TYPE' => 'LIST',
        'ADDITIONAL_VALUES' => 'Y',
        'VALUES' => $arIBlockType,
        'REFRESH' => 'Y',
        'DEFAULT' => 'abyte_mediagallery'
    );

    $arComponentParameters['PARAMETERS']['FILTER_IBLOCK_ID'] = array(
        'PARENT' => 'FILTER',
        'NAME' => GetMessage('IBLOCK_IBLOCK'),
        'TYPE' => 'LIST',
        'ADDITIONAL_VALUES' => 'Y',
        'VALUES' => $arFilterIBlock,
        'REFRESH' => 'Y',
    );


    if($arCurrentValues['FILTER_IBLOCK_ID'] > 0) {
        $arPropList = array();
        $rsProps = CIBlockProperty::GetList(array(), array('IBLOCK_ID' => $arCurrentValues['FILTER_IBLOCK_ID']));
        while ($arProp = $rsProps->Fetch()) {
            $arPropList[$arProp['CODE']] = $arProp['NAME'];
        }

        $arComponentParameters['PARAMETERS']['FILTER_FIELD'] = array(
            'PARENT' => 'FILTER',
            'NAME' => GetMessage('FILTER_FIELD'),
            'TYPE' => 'LIST',
            'VALUES' => $arPropList,
        );
    }


    $arComponentParameters['PARAMETERS']['SHOW_SUBMIT'] = array(
        'PARENT' => 'FILTER',
        'NAME' => GetMessage('SHOW_SUBMIT'),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => 'Y',
    );

    $arComponentParameters['PARAMETERS']['JS_ELEM'] = array(
        'PARENT' => 'FILTER',
        'NAME' => GetMessage('JS_ELEM'),
        'TYPE' => 'STRING',
        'DEFAULT' => '',
    );

    $arComponentParameters['PARAMETERS']['LIMIT'] = array(
        'PARENT' => 'FILTER',
        'NAME' => GetMessage('LIMIT'),
        'TYPE' => 'STRING',
        'DEFAULT' => '0',
    );

    $arComponentParameters['PARAMETERS']['SHOW_SEARCH'] = array(
        'PARENT' => 'FILTER',
        'NAME' => GetMessage('SHOW_SEARCH'),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => 'Y',
    );

    $arComponentParameters['PARAMETERS']['SHOW_CLEAR'] = array(
        'PARENT' => 'FILTER',
        'NAME' => GetMessage('SHOW_CLEAR'),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => 'Y',
    );
}


if ($arCurrentValues['SHOW_INFO'] == 'Y') {

    $arComponentParameters['PARAMETERS']['IBLOCK_TYPE'] = array(
        'PARENT' => 'DATA_SOURCE',
        'NAME' => GetMessage('IBLOCK_TYPE'),
        'TYPE' => 'LIST',
        'ADDITIONAL_VALUES' => 'Y',
        'VALUES' => $arIBlockType,
        'REFRESH' => 'Y',
        'DEFAULT' => 'abyte_mediagallery'
    );

    $arComponentParameters['PARAMETERS']['IBLOCK_ID'] = array(
        'PARENT' => 'DATA_SOURCE',
        'NAME' => GetMessage('IBLOCK_IBLOCK'),
        'TYPE' => 'LIST',
        'ADDITIONAL_VALUES' => 'Y',
        'VALUES' => $arIBlock,
        'REFRESH' => 'Y',
    );

    $arComponentParameters['PARAMETERS']['SELECT_INFO_STATIONS'] = array(
        'PARENT' => 'DATA_SOURCE',
        'NAME' => GetMessage('SELECT_INFO_STATIONS'),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => 'Y'
    );

    $arComponentParameters['PARAMETERS']['SHOW_ALL_LABELS'] = array(
        'PARENT' => 'DATA_SOURCE',
        'NAME' => GetMessage('SHOW_ALL_LABELS'),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => 'N'
    );

}

if($arCurrentValues['IBLOCK_ID'] > 0) {
    
    $arSectList = Array(
        '-1' => GetMessage('ANY'),
        '0' => GetMessage('NO_SECTION')
    );

    $arFilter =  Array(
        'IBLOCK_ID'=>$arCurrentValues['IBLOCK_ID'],
        'GLOBAL_ACTIVE'=>'Y'
    );

    $rsSect = CIBlockSection::GetList(array('name' => 'asc'),$arFilter, false, Array('ID', 'NAME'));
    while ($arSect = $rsSect->GetNext())
    {
        $arSectList[$arSect['ID']] = $arSect['NAME'];
    }

    $arComponentParameters['PARAMETERS']['SECTION'] = array(
        'PARENT' => 'DATA_SOURCE',
        'NAME' => GetMessage('SECTION'),
        'TYPE' => 'LIST',
        'VALUES' => $arSectList,
        'MULTIPLE' => 'Y',
        'DEFAULT' => Array('-1')
    );



    $arPropList = array();
    $rsProps = CIBlockProperty::GetList(array(), array('IBLOCK_ID' => $arCurrentValues['IBLOCK_ID']));
    while ($arProp = $rsProps->Fetch()) {
        $arPropList['PROPERTY_'.$arProp['CODE']] = $arProp['NAME'];
    }

    $arComponentParameters['PARAMETERS']['METRO_PROPERTY'] = array(
        'PARENT' => 'DATA_SOURCE',
        'NAME' => GetMessage('METRO_PROPERTY'),
        'TYPE' => 'LIST',
        'VALUES' => $arPropList,
    );

    $arFieldsList = array();
   $arFields = CIBlock::GetFields($arCurrentValues['IBLOCK_ID']);


    $arFieldsList[0] = GetMessage('NOT_SET');
    $arFieldsList['NAME'] = $arFields['NAME']['NAME'].' [NAME]';
    $arFieldsList['CODE'] = $arFields['CODE']['NAME'].' [CODE]';
    $arFieldsList['PREVIEW_TEXT'] = $arFields['PREVIEW_TEXT']['NAME'].' [PREVIEW_TEXT]';
    $arFieldsList['DETAIL_TEXT'] = $arFields['DETAIL_TEXT']['NAME'].' [DETAIL_TEXT]';


    $arComponentParameters['PARAMETERS']['PREVIEW_FIELD'] = array(
        'PARENT' => 'DATA_SOURCE',
        'NAME' => GetMessage('PREVIEW_FIELD'),
        'TYPE' => 'LIST',
        'VALUES' => array_merge($arFieldsList, $arPropList ),
    );

    $arComponentParameters['PARAMETERS']['DETAIL_FIELD'] = array(
        'PARENT' => 'DATA_SOURCE',
        'NAME' => GetMessage('DETAIL_FIELD'),
        'TYPE' => 'LIST',
        'VALUES' => array_merge($arFieldsList, $arPropList ),
    );



}

?>