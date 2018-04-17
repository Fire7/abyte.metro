<?
$MODULE_ID = basename(dirname(__FILE__));
CJSCore::RegisterExt('select2', array(
	'js' => '/bitrix/js/'.$MODULE_ID.'/select2/select2.min.js',
	'css' =>  '/bitrix/js/'.$MODULE_ID.'/select2/select2.css',
	'lang' => '',
	'rel' => array('jquery')
));


CJSCore::RegisterExt('jmetro', array(
            'js' => '/bitrix/js/'.$MODULE_ID.'/jmetro/jmetro.js',
            'css' => '/bitrix/js/'.$MODULE_ID.'/jmetro/jmetro.css',
            'rel' => array('jquery')
            ));
            


Class CAbyteMetro
{
	public static function GetAvailableCities() {		
		return Array('MSK', 'SPB');
	}

	public static function GetCitySrc($city)
	{
		switch ($city) {
			case 'MSK':
				return file_get_contents(dirname(__FILE__).'/lang/ru/src/msk.json');
				break;
            case 'SPB':
                return file_get_contents(dirname(__FILE__).'/lang/ru/src/spb.json');
                break;
			default:
				return NULL;
				break;
		}
	}
}
?>
