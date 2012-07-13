<?php
	mb_internal_encoding("UTF-8");
	$nodes=array();
	$links=array();

	$xml = new SimpleXMLElement(file_get_contents("data/complete_report.xml"));

	$entities = $xml->xpath('//SpendingEntity');

	while(list( , $entity) = each($entities)) {
		$attr=$entity->attributes();
		$name = (string) $attr["name"];
		$amount = intval((string) $attr["fullAmount"]);
		$category = (string) $attr["categoryName"];
		if ($category!="Правителство")// && $category!="Съдебна власт" && $category!="Други държавни органи")
			continue;
		
		if ($name=="Национален осигурителен институт" || $name=="Субсидии за общини")
			continue;
		
		$nodes[]=$name;
		$entI=count($nodes)-1;
/*
		$catI = array_search($category,$nodes);
		if ($catI==false) {
			$nodes[]=$category;
			$catI=count($nodes)-1;
		}
		$links[]=array("source"=>$catI, "target"=>$entI, "value"=>$amount);

*/
		foreach ($entity->children() as $payment) {
			$attr=$payment->attributes();
			$name = (string) $attr["name"];
			$amount = 0;
			foreach ($payment->children() as $paymentDay) {
				$attr=$paymentDay->attributes();
				$amount += intval((string) $attr["amount"]);
			}

			if ($amount>-10000 && $amount<100000)
				continue;
		
			$payI = array_search($name,$nodes);
			if ($payI==false) {
				$nodes[]=$name;
				$payI=count($nodes)-1;
			}
			if ($amount>0)
				$links[]=array("source"=>$entI, "target"=>$payI, "value"=>$amount);
			else
				$links[]=array("source"=>$payI, "target"=>$entI, "value"=>-1*$amount);
		}
		if (count($nodes)>30)
			break;
	}

	for ($i=0;$i<count($nodes);$i++) {
		if (mb_strlen($nodes[$i])>70)
			$nodes[$i]=mb_substr($nodes[$i],0,70)."...";
		$nodes[$i]=array("name"=>$nodes[$i]);
	}
	echo json_encode(array("nodes"=>$nodes, "links"=>$links));
?>
