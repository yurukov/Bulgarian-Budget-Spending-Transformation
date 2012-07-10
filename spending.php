<?php

require_once 'lib/excel_reader2.php';
mb_internal_encoding("UTF-8");

$page = file_get_contents("http://www.minfin.bg/bg/transparency");
if (preg_match_all("_\/document\/([\d:]+)_",$page,$matches,PREG_PATTERN_ORDER)===false || count($matches)<2)
die("Грешка при четенето");

echo "Намерени са ".count($matches[1])." документи.<br/>\n";
for ($i=0;$i<count($matches[1]);$i++) {
	echo "Сваляне на ".$matches[1][$i]."... ";
	$filenameXls="cache/".substr($matches[1][$i],0,5).".xls";
	$filenameXml="cache/".substr($matches[1][$i],0,5).".xml";

	if (file_exists($filenameXml) && filesize($filenameXml)>20) {
		echo "вече свален. Пропускам.<br/>\n";
	} else {
		$document = file_get_contents("http://www.minfin.bg/document/".$matches[1][$i]);
		file_put_contents($filenameXls,$document);
		$xml=parseExcel($filenameXls);
		file_put_contents($filenameXml,$xml);

		echo "свален и прочетен. Записани са ".strlen($xml)." байта.<br/>\n";
	}
}

$files = explode("\n",trim(`ls cache/*.xml`));
sort($files);
$all = "<?xml version=\"1.0\"?>\n<BudgetSpendings>\n";
foreach ($files as $file)
	$all.=str_replace("<?xml version=\"1.0\"?>\n","",file_get_contents($file));
$all.="\n</BudgetSpendings>";
file_put_contents("all.xml",$all);

`xsltproc xsl/all.xsl all.xml > spending.xml; rm all.xml`;

echo "Трансформирани са  ".count($files)." записа.<br/>\n";


function parseExcel($filename) {
	$data = new Spreadsheet_Excel_Reader($filename,false,"CP1251");

	$xml = new SimpleXMLElement("<BudgetSpending/>");
	$spendingSummary = $xml->addChild("SpendingSummary");
	$sebra = $spendingSummary->addChild("Sebra");
	$other = $spendingSummary->addChild("Other");
	$intitutions = $xml->addChild("SpendingBreakdown");

	$isSummary=true;
	$isSebra=false;
	$isOther=false;
	$latestNode=false;
	$cacheName=false;

	for ($j=0;$j<$data->rowcount();$j++)
		if (trim($data->val($j,1))!="" || trim($data->val($j,2))!="") {

			$c1=trim(mb_convert_encoding($data->val($j,1),"UTF-8","CP1251"));
			$c2=trim(mb_convert_encoding($data->val($j,2),"UTF-8","CP1251"));
			$c3=trim(mb_convert_encoding($data->val($j,3),"UTF-8","CP1251"));
			$c4=trim(mb_convert_encoding($data->val($j,4),"UTF-8","CP1251"));

			if ($c1=="Код" || $c2=="Описание" || $c3=="Брой" || $c4=="Сума" || mb_strpos($c1,"ПЛАЩАНИЯ ПО")===0)
				continue;

			if ($c1=="ОБЩО ПЛАЩАНИЯ ЗА ДЕНЯ (в лева)")
				$xml->addAttribute('date', mb_substr($c3,-10));
			else
			if (mb_strpos($c1,"I. ")===0) {
				$isSebra=true;
			} else
			if (mb_strpos($c1,"II. ")===0) {
				$isSebra=false;
			} else
			if ($c1=="ДРУГИ ПЛАЩАНИЯ") {
				$isOther=true;
			} else
			if (mb_strpos($c3,"Период: ")!==false) {
				if ($isSummary)
					die("Грешна структура! 1 $j");

				$name = ($cacheName?$cacheName." ":"").$c1;
				$latestNode = $intitutions->addChild("SpendingEntity");
				$latestNode->addAttribute('name', mb_strstr($name, " ( ", true));
				$latestNode->addAttribute('code', mb_substr(mb_strstr($name, " ( "),3,-2));
				$cacheName=false;
			} else
			if (mb_strpos($c1,"Общо")===0) {
				if ($isOther) {
					$isOther=false;
					continue;
				}
				if ($isSummary) {
					$spendingSummary->addAttribute('count', $c3);
					$spendingSummary->addAttribute('fullAmount', str_replace(",","",$c4));
				} else
				if ($latestNode) {
					$at=$latestNode->attributes();
					if ($at['count'])
						echo "$j".$at['name'];
					$latestNode->addAttribute('count', $c3);
					$latestNode->addAttribute('fullAmount', str_replace(",","",$c4));
					$latestNode=false;
				} else 
					die("Грешна структура! 2 $j");
				$isSummary=false;
			} else {
				if ($isOther)
					continue;

				if ($isSummary) {
					if ($isSebra)
						$node = $sebra->addChild("Payment");
					else
						$node = $other->addChild("Payment");
				} else
				if ($latestNode)
					$node = $latestNode->addChild("Payment");
				else {
					$cacheName=($cacheName?$cacheName." ":"").$c1;
					continue;
				}

				if ($c1!="" && $c1!="xxxx")
					$node->addAttribute('code', $c1);
				$node->addAttribute('name', $c2);
				$node->addAttribute('count', $c3);
				$node->addAttribute('amount', str_replace(",","",$c4));
			}


		}

	$dom = dom_import_simplexml($xml)->ownerDocument;
	$dom->formatOutput = true;
	return html_entity_decode($dom->saveXML(), ENT_NOQUOTES, 'UTF-8');
}

?>
