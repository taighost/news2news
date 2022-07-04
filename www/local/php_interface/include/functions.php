<?php

AddEventHandler("iblock", "OnAfterIBlockElementAdd", Array("News4Export", "OnAfterIBlockElementAddHandler"));
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", Array("News4Export", "OnAfterIBlockElementUpdateHandler"));

class News4Export
{
    // создаем обработчик события "OnAfterIBlockElementAdd" - отрабатывает при добавлении элемента в инфоблок
    function OnAfterIBlockElementAddHandler(&$arFields)
    {
        if ($arFields["ID"]>0 && $arFields["ACTIVE"]=='Y' && $arFields["IBLOCK_ID"]==111) {//замените 111 на ID своего инфоблока

				$doc = new DOMDocument('1.0');

				$root = $doc->appendChild($doc->createElement('news'));
				//название-заголовок
				$title = $root->appendChild($doc->createElement('title'))->appendChild($doc->createCDATASection($arFields["NAME"]));	
				//свойство-подзаголовок
				$title = $root->appendChild($doc->createElement('subtitle'))->appendChild($doc->createCDATASection($arFields["PROPERTY_VALUES"][333]["n0"]["VALUE"]));	//замените 333 на ID своего свойства			
				$root->appendChild($doc->createElement('date'))->appendChild($doc->createCDATASection($arFields["ACTIVE_FROM"]));	
				//детальный текст новости
				$root->appendChild($doc->createElement('detail'))->appendChild($doc->createCDATASection($arFields["DETAIL_TEXT"]));
				// превью-картинка новости
				$rsFile = CFile::GetByID($arFields["PREVIEW_PICTURE"]["old_file"]);				
				$root->appendChild($doc->createElement('prevpic'))->appendChild($doc->createCDATASection("/home/bitrix/www/upload/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"]));
				
				$doc->save("/home/bitrix/www/upload/news/4export/".$arFields["ID"].".xml");
				
		}            
    }
	
    // создаем обработчик события "OnAfterIBlockElementUpdate" - отрабатывает при обновлении элемента в инфоблок 
    function OnAfterIBlockElementUpdateHandler(&$arFields)
    {
        if($arFields["RESULT"] && $arFields["ID"]>0 && $arFields["ACTIVE"]=='Y' && $arFields["IBLOCK_ID"]==111) {//замените 111 на ID своего инфоблока
				
				$doc = new DOMDocument('1.0');

				$root = $doc->appendChild($doc->createElement('news'));

				$title = $root->appendChild($doc->createElement('title'))->appendChild($doc->createCDATASection($arFields["NAME"]));

				$title = $root->appendChild($doc->createElement('subtitle'))->appendChild($doc->createCDATASection($arFields["PROPERTY_VALUES"][333][$arFields["ID"].":333"]["VALUE"]));	//замените 333 на ID своего свойства			

				$root->appendChild($doc->createElement('date'))->appendChild($doc->createCDATASection($arFields["ACTIVE_FROM"]));
				
				$root->appendChild($doc->createElement('detail'))->appendChild($doc->createCDATASection($arFields["DETAIL_TEXT"]));
				
				$rsFile = CFile::GetByID($arFields["PREVIEW_PICTURE"]["old_file"]);
				
				$root->appendChild($doc->createElement('prevpic'))->appendChild($doc->createCDATASection("/home/bitrix/www/upload/".$arFile["SUBDIR"]."/".$arFile["FILE_NAME"]));						
				
				$doc->save("/home/bitrix/www/upload/news/4export/".$arFields["ID"].".xml");				
			
		} 			
    }	
}