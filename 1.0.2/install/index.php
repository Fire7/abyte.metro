<?
IncludeModuleLangFile(__FILE__);

function utf8_encode_deep(&$input) {
	if (is_string($input)) {
		$input = utf8_encode($input);
	} else if (is_array($input)) {
		foreach ($input as &$value) {
			utf8_encode_deep($value);
		}

		unset($value);
	} else if (is_object($input)) {
		$vars = array_keys(get_object_vars($input));

		foreach ($vars as $var) {
			utf8_encode_deep($input->$var);
		}
	}
}

function utf8_decode_deep(&$input) {
	if (is_string($input)) {
		$input = utf8_decode($input);
	} else if (is_array($input)) {
		foreach ($input as &$value) {
			utf8_decode_deep($value);
		}

		unset($value);
	} else if (is_object($input)) {
		$vars = array_keys(get_object_vars($input));

		foreach ($vars as $var) {
			utf8_decode_deep($input->$var);
		}
	}
}
Class abyte_metro extends CModule
{
	const MODULE_ID = 'abyte.metro';
	var $MODULE_ID = 'abyte.metro';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';

	function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage('MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('MODULE_DESC');

		$this->PARTNER_NAME = 'Abyte';
		$this->PARTNER_URI = 'http://abyte.xyz';

		$this->AvailableCities = Array(
			'msk' => Array(
				'id' => 1000,
				'name' => GetMessage('MSK')
			),
			'spb' => Array(
				'id' => 2000,
				'name' => GetMessage('SPB')
			)
		);
	}

	function InstallDB($arParams = array())
	{
		global $DB;


		if (!CModule::IncludeModule("iblock"))
		{
			echo CAdminMessage::ShowMessage(array(
				"TYPE" => "ERROR",
				"MESSAGE" => GetMessage("IBLOCK_MOD_ERR"),
				"HTML" => true,
			));
			return false;
		}

		// array of site id's
		$arQuery = CSite::GetList($sort="sort", $order="desc", Array());
		while ($res = $arQuery->Fetch()) {
			$sids[] = $res["ID"];
		}



		// if no Iblock Type -> creating new
		$arType=CIBlockType::GetByID("abyte_metro");
		if(!$Type=$arType->Fetch())
		{

			$arFields = Array(
				'ID'=>'abyte_metro',
				'SECTIONS'=>'Y',
				'IN_RSS'=>'N',
				'SORT'=>100,
				'LANG'=>Array(
					'ru'=>Array(
						'NAME'=>GetMessage('METRO'),
						'SECTION_NAME'=>GetMessage('CITIES'),
						'ELEMENT_NAME'=>GetMessage('STATIONS')
					)
				)
			);

			$obBlocktype = new CIBlockType;
			$DB->StartTransaction();
			$res = $obBlocktype->Add($arFields);
			if(!$res)
			{
				$DB->Rollback();
				echo 'Error: '.$obBlocktype->LAST_ERROR.'<br>';
			}
			else
				$DB->Commit();
		}


		// STATION SRC
		$q = CIBlock::GetList(Array(), Array(
			"TYPE" => "abyte_metro",
			"CODE" => "ab_metro_src"
		), true);


		// if iblock is not found --> creating new
		if ($q->SelectedRowsCount() == 0) {
			$ib=new CIBlock();
			$arFields = Array(
				"ACTIVE" => "Y",
				"NAME" =>GetMessage('METRO'),
				"CODE" => "ab_metro_src",
				"SITE_ID" => $sids,
				"IBLOCK_TYPE_ID" => "abyte_metro",
				"GROUP_ID" => Array("2"=>"R","1"=>"X")
			);

			$arrMetro = array();

			if($IBlockID=$ib->add($arFields)){

				// Save options for further use
				COption::SetOptionInt(self::MODULE_ID, "IBLOCK_ID", $IBlockID);


				foreach ($this->AvailableCities as $cityCode => $city) {

					$fileContent = file_get_contents(dirname(__FILE__) . "/../lang/ru/src/$cityCode.json");
					$arMetro = json_decode($fileContent);

					if (!$arMetro) {
						$arMetro = json_decode(utf8_encode($fileContent));
						utf8_decode_deep($arMetro);
					}

					$arrMetro = array_merge($arrMetro, $arMetro);
					if (is_array($arMetro)) {
						// 1. section (city)
						$citySection = new CIBlockSection;
						$arFields = Array(
							"ACTIVE" => 'Y',
							'CODE' => $cityCode,
							'EXTERNAL_ID' => $city['id'],
							"IBLOCK_ID" => $IBlockID,
							"NAME" => $city['name'],
							"SORT" => 100,

						);

						$sectionID = $citySection->Add($arFields);

						// 2. Stations elements
						foreach($arMetro as $key => $object) {
							$el = new CIBlockElement;

							$arFields = Array(
								'IBLOCK_ID' => $IBlockID,
								'IBLOCK_SECTION_ID' => $sectionID,
								'EXTERNAL_ID' => $object->id,
								'NAME' => $object->name.' ['.$city['name'].']',
								'PREVIEW_TEXT' => $object->name
							);

							if($el->Add($arFields)) {
								continue;
							}
							else {
								echo "Error: ".$el->LAST_ERROR;
								break;
							}
						}
					}
				}

				//OBJECT IBLOCK
				$q1 = CIBlock::GetList(Array(), Array(
					"TYPE" => "abyte_metro",
					"CODE" => "ab_metro_objects"
				), true);


				// if iblock is not found --> creating new
				if ($q1->SelectedRowsCount() == 0) {
					$ib=new CIBlock();
					$arFields = Array(
						"ACTIVE" => "Y",
						"NAME" =>GetMessage('OBJECTS_ON_MAP'),
						"CODE" => "ab_metro_objects",
						"SITE_ID" => $sids,
						"IBLOCK_TYPE_ID" => "abyte_metro",
						"GROUP_ID" => Array("2"=>"R","1"=>"X"),
						'DESCRIPTION_TYPE' => 'html',
						'ELEMENT_NAME' => GetMessage('OBJECT'),
						'ELEMENTS_NAME'=> GetMessage('OBJECTS')
					);
					if($IBlockID=$ib->add($arFields)){

						COption::SetOptionInt(self::MODULE_ID, "OBJECT_IBLOCK_ID", $IBlockID);

						$iblockproperty = new CIBlockProperty;

						$prop = Array(
							'NAME' => GetMessage('METRO_STATION'),
							"ACTIVE" => "Y",
							"SORT" => "500",
							'CODE' => 'STATION',
							'PROPERTY_TYPE' => "E",
							'USER_TYPE' => 'EList',
							'LINK_IBLOCK_ID' =>COption::GetOptionInt(self::MODULE_ID, "IBLOCK_ID"),
							'IBLOCK_ID' => $IBlockID
						);

						$iblockproperty->Add($prop);

					}else{
						echo $ib->LAST_ERROR;
					}

				}else{
					echo $ib->LAST_ERROR;
				}
			}
		}

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB;
		if(!array_key_exists("savebase", $arParams) || $arParams["savebase"] != "Y")
		{
			CModule::IncludeModule("iblock");
			$ib=COption::GetOptionInt(self::MODULE_ID, "IBLOCK_ID");
			$oib=COption::GetOptionInt(self::MODULE_ID, "OBJECT_IBLOCK_ID");

			$DB->StartTransaction();
			CIBlockType::Delete("abyte_metro");
			$DB->Commit();
			$DB->StartTransaction();
			CIBlock::Delete($ib);
			CIBlock::Delete($oib);
			$DB->Commit();
		}


		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles($arParams = array())
	{
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/admin'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.' || $item == 'menu.php')
						continue;
					file_put_contents($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.self::MODULE_ID.'_'.$item,
						'<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.self::MODULE_ID.'/admin/'.$item.'");?'.'>');
				}
				closedir($dir);
			}
		}
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/bitrix/components/'.$item, $ReWrite = True, $Recursive = True);
				}
				closedir($dir);
			}
		}

		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/js'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.self::MODULE_ID.'/'.$item, $ReWrite = True, $Recursive = True);
				}
				closedir($dir);
			}
		}

		return true;
	}

	function UnInstallFiles()
	{

		// admin scripts
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/admin'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					unlink($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.self::MODULE_ID.'_'.$item);
				}
				closedir($dir);
			}
		}


		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.' || !is_dir($p0 = $p.'/'.$item))
						continue;

					$dir0 = opendir($p0);
					while (false !== $item0 = readdir($dir0))
					{
						if ($item0 == '..' || $item0 == '.')
							continue;
						DeleteDirFilesEx('/bitrix/components/'.$item.'/'.$item0);
					}
					closedir($dir0);
				}
				closedir($dir);
			}
		}


		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/js'))
		{
			if ($dir = opendir($p))
			{
				DeleteDirFilesEx('/bitrix/js/'.self::MODULE_ID.'/');

			}
			closedir($dir);

		}


		return true;
	}

	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallFiles();
		$this->InstallDB();

		RegisterModule(self::MODULE_ID);


	}

	function DoUninstall()
	{
		global $APPLICATION;
		$step = IntVal($_REQUEST['STEP']);

		if ($step < 2)
			$APPLICATION->IncludeAdminFile(GetMessage(self::MODULE_ID."_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/unstep1.php");

		if ($step == 2)
		{
			UnRegisterModule(self::MODULE_ID);
			$this->UnInstallDB(array(
				"savebase" => $_REQUEST["savebase"],
			));
			$this->UnInstallFiles();
		}
	}
}
?>
