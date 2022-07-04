<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class MainsiteInfo {	

	function GetNewsFromFolder($dir='/home/bitrix/www/upload/') {				
		
		$response = '<?xml version="1.0" encoding="utf-8"?>';				
		$response .= '<Files>';
		
		$files = scandir($dir);
		
		if (count($files)>0 && CModule::IncludeModule('iblock')) {
			
			$IBLOCK_ID = 111; //вставьте свой ID инфоблока
			
			$exstnews = MainsiteInfo::GetExistingNews($IBLOCK_ID);

			foreach($files as $filename) {
				
				// если новости ещё нет в базе, добавляем его, если есть, обновляем текст и дату обновления
				if (preg_match('/(([0-9]+)\.xml)/i',$filename) && !isset($exstnews[$filename]["FILENAME"])) {					
					
					$nid = MainsiteInfo::GetNewsFromFile2SiteAdd($dir, $filename, $IBLOCK_ID);
					//уведомляем о публикации
					if (is_numeric($nid)) {
						unlink($dir.'/'.$filename);
						$response .= '<File Name="'.$filename.'"/>';
					} else {			
						$response .= '<File Name="'.$filename.'" Error="'.$nid.'"/>';										
					}
					
				// файлы которые есть в базе - обновляем
				} elseif (preg_match('/(([0-9]+)\.xml)/i',$filename) && isset($exstnews[$filename]["FILENAME"])) { 
					
					$nid = MainsiteInfo::GetNewsFromFile2SiteUpdate($dir, $exstnews[$filename], $IBLOCK_ID);
					//уведомляем о публикации
					if (is_numeric($nid)) {
						unlink($dir.'/'.$filename);
						$response .= '<File Name="'.$filename.'"/>';
					} else {			
						$response .= '<File Name="'.$filename.'" Error="'.$nid.'"/>';										
					}	
				//если файлы имеют отличное от цифрового имя - не добавляем
				} elseif(file_exists($dir.'/'.$filename) && strlen($filename)>3) {
					
					$response .= '<File Name="'.$filename.'" Error="Имя файла не прошло фильтр"/>';	

				}
			}
			
		}
		
		$response .= '</Files>';
		BXClearCache(false, '/');
		
		return $response;
	}
	
	function GetExistingNews($IBLOCK_ID) {
		
		// получаем список кодов новостей, которые уже были загружены
		$arSelect = Array("ID", "CODE", "PROPERTY_filename");
		$arFilter = Array("IBLOCK_ID"=>$IBLOCK_ID, 'ACTIVE'=>'Y');
		
		$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
		$news_loaded = array();
		
		while($ob = $res->GetNextElement())
		{
			$arFields = $ob->GetFields();			
			$news_loaded[$arFields["PROPERTY_FILENAME_VALUE"]] = array("FILENAME"=>$arFields["PROPERTY_FILENAME_VALUE"],"ID"=>$arFields["ID"]);
		}
		return $news_loaded;
		
	}
	
	function GetNewsFromFile2SiteAdd($dir, $filename, $IBLOCK_ID) {
			
		$fullname = $dir.'/'.$filename;
		if (file_exists($fullname)) {			
			
			$news_content = file_get_contents($fullname);
			$news = json_decode(json_encode(simplexml_load_string($news_content, 'SimpleXMLElement', LIBXML_NOCDATA),true), true);
			
			//если вернулась дата новости, название и текст. без этих данных не публикуем
			if (count($news)>=3) {		

				$el = new CIBlockElement;
				$arrProp = Array();
			
				//если есть, указываем его в свойстве добавляемой новости				
				$arrProp["filename"] = 	$filename;	
				$arrProp["SUBTITLE"] = 	$news['subtitle'];
				// добавляем новость в инфоблок в раздел НЕОпубликовынные
				
				$arLoadElemtArray = Array(
					"MODIFIED_BY"    		=> 1,					    
					"IBLOCK_ID"      		=> $IBLOCK_ID,
					"PROPERTY_VALUES"		=> $arrProp,
					"NAME"           		=> $news['title'],
					"ACTIVE"         		=> "Y",            
					"ACTIVE_FROM"    		=> $news['date'],  
					"PREVIEW_TEXT"   		=> "",
					"PREVIEW_TEXT_TYPE"		=> "html",
					"DETAIL_TEXT"   		=> $news['detail'], 
					"DETAIL_TEXT_TYPE"		=> "html",			
				);

				if (isset($news['prevpic'])) {
					$arFile = CFile::MakeFileArray($news['prevpic']);
					$arLoadElemtArray['PREVIEW_PICTURE'] = $arFile;
				}
				
				//TODO: сделать запись в лог-файл
				$log = '';
				if ($newsID = $el->Add($arLoadElemtArray,true)) {
					echo "<br/>".date('Y-m-d H:i:s')." Импортирована новость добавлена: ".$newsID." ".$title;
					return $newsID; 
				} else {
					echo "<br/>".date('Y-m-d H:i:s')." Ошибка импорта новости ".$exstnewsfiles["FILENAME"].": ".$el->LAST_ERROR;
					return "Ошибка импорта новости ".$exstnewsfiles["FILENAME"].": ".$el->LAST_ERROR;
				}
			} else {
				echo "<br/>".date('Y-m-d H:i:s')."Ошибка! Данные не полные!";
				return "Ошибка! Данные не полные!";
			}
		}
	}
	
	function GetNewsFromFile2SiteUpdate($dir, $exstnewsfiles, $IBLOCK_ID) {
					
		$fullname = $dir.'/'.$exstnewsfiles["FILENAME"];echo $fullname;
		if (file_exists($fullname)) {			
			
			$news_content = file_get_contents($fullname);
			$news = json_decode(json_encode(simplexml_load_string($news_content, 'SimpleXMLElement', LIBXML_NOCDATA),true), true);
			
			//если вернулся код новости, название и текст. без этих данных не публикуем
			if (count($news)>=3) {

				$el = new CIBlockElement;
				$arrProp = Array();

				$arrProp["filename"] = 	$exstnewsfiles["FILENAME"];	
				$arrProp["SUBTITLE"] = 	$news['subtitle'];
				// добавляем новость в инфоблок в раздел НЕОпубликовынные
				$arLoadElemtArray = Array(
					"MODIFIED_BY"    		=> 1, 
					"PROPERTY_VALUES"		=> $arrProp,
					"NAME"           		=> $news['title'],
					"ACTIVE"         		=> "Y",            
					"ACTIVE_FROM"    		=> $news['date'],  
					"PREVIEW_TEXT"   		=> "",
					"PREVIEW_TEXT_TYPE"		=> "html",
					"DETAIL_TEXT"   		=> $news['detail'], 
					"DETAIL_TEXT_TYPE"		=> "html",			
				);
				
				if (isset($news['prevpic'])) {
					$arFile = CFile::MakeFileArray($news['prevpic']);
					$arLoadElemtArray['PREVIEW_PICTURE'] = $arFile;
				}
				
				//TODO: сделать запись в лог-файл
				$log = '';
				
				if ($el->Update($exstnewsfiles["ID"],$arLoadProductArray,true)) {
					echo "<br/>".date('Y-m-d H:i:s')." Импортированая новость обновлена: ".$exstnewsfiles["FILENAME"]." ".$title;
					
					return $exstnewsfiles["ID"]; 
				} else {
					echo "<br/>".date('Y-m-d H:i:s')." Ошибка импорта новости ".$exstnewsfiles["FILENAME"].": ".$el->LAST_ERROR;
					
					return "Ошибка импорта новости ".$exstnewsfiles["FILENAME"].": ".$el->LAST_ERROR;
				}
			} else {
				echo "<br/>".date('Y-m-d H:i:s')."Ошибка! Данные не полные!"
				
				return "Ошибка! Данные не полные!";
			}
		}
	}			

}	

?>