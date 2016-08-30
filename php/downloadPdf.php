<?php
	exec('find ../ReadWrite/ -mmin +10 -type f -name "*.pdf" -exec rm {} \;');
	$downloadURL = '../index.php';
	$titleid = $_GET['titleid'];
	if(isset($_GET['titleid']) && $_GET['titleid'] != "")
	{
		include("connect.php");
		$vars = explode('_', $titleid);
		$volume = $vars[2];
		$part = $vars[3];
		$page = $vars[4];
		$str = '';
		$pageRangeList = preg_split('/;/',$page);
		$page = '';
		
		for($i = 0; $i < count($pageRangeList); $i++)
		{
			$pageRange = preg_split('/-/',$pageRangeList[$i]);
			$str .= "'".$pageRange[0]."' and '".$pageRange[1]."' or cur_page between ";
			$page .= $pageRange[0] . '_' . $pageRange[1] . '_';
		}
		$str = preg_replace("/ or cur_page between $/", "" ,$str);
		$page = preg_replace("/_$/", "" ,$page);
		$pdfList = '';
		$query1 = "select cur_page from ocr where volume = '$volume' and part = '$part' and (cur_page between $str)";
		
		$result1 = $db->query($query1) or die("query problem"); 
		
		while($row = $result1->fetch_assoc())
		{
			$pdfList .= '../Volumes/pdf/' . $volume . '/' . $part . '/' . $row["cur_page"] . '.pdf ';
		}
		//~ $temp = '../ReadWrite/Shankara_Krupa_' . time() . '_' . rand(1,9999) . '.pdf'; 
		
		$downloadURL = '../ReadWrite/prabudhakarnataka_' . $volume . '_' . $part . '_' . $page . '.pdf';
		system ('pdftk ' . $pdfList . ' cat output ' . $downloadURL);
		//~ system ('pdfopt ' . $temp . ' ' . $downloadURL);
	}
	@header("Location: $downloadURL");
?>

