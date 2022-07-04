<?
include_once $_SERVER["DOCUMENT_ROOT"]."/local/php_interface/info_from_mainsite.php";

function NewsFromMSAgent()
{
		$resp = MainsiteInfo::GetNewsFromFolder('/home/bitrix/www/upload/news/4export');	
        return "NewsFromMSAgent();";
}
