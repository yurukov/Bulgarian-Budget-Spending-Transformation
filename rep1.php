<?php
	$struct = `xsltproc xsl/report1.xsl spending.xml`;
	$xml = new SimpleXMLElement($struct);
	echo json_encode(trans($xml), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

	function trans($node) {
		$data = array();
		foreach ($node->children() as $one) {
			$data["name"]=$one["name"];
			if (isset($one["count"])) {
				$data["count"]=intval($one["count"]);
				$data["amount"]=intval($one["amount"]);
			} else
				$data["children"]=trans($one);
		}
		return $data;
	}

?>
