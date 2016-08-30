<?php include("include_header.php");?>
<main class="cd-main-content">
		<div class="cd-scrolling-bg cd-color-2">
			<div class="cd-container">
<?php

include("connect.php");
require_once("common.php");

if(isset($_GET['vol'])){$volume = $_GET['vol'];}else{$volume = '';}
if(isset($_GET['part'])){$part = $_GET['part'];}else{$part = '';}

$dpart = preg_replace("/^0/", "", $part);
$dpart = preg_replace("/\-0/", "-", $dpart);

$yearMonth = getYearMonth($volume, $part);
$maasa = getmaasa($volume, $part);
$seriesIssue = '';
$seriesIssue = getseriesIssue($volume, $part);
$info = '';

if($yearMonth['month'] != '')
{
	$info = $info . getMonth($yearMonth['month']);
}
if($yearMonth['year'] != '')
{
	$info = $info . ' <span class="font_size">' . toKannada(intval($yearMonth['year'])) . '</span>';
}
if($maasa['maasa'] != '')
{
	$info = $info . ', ' . $maasa['maasa'] . '&nbsp;ಮಾಸ';
}
if($maasa['samvatsara'] != '')
{
	$info = $info . ', ' . $maasa['samvatsara'] . '&nbsp;ಸಂವತ್ಸರ';
}
if($seriesIssue['snum'] != '')
{
	$info = $info . ', ' . toKannada(intval($seriesIssue['snum'])) . ' ನೆಯ ಸರಣಿ ಸಂಚಿಕೆ';
}
$info = preg_replace("/^,/", "", $info);
$info = preg_replace("/^ /", "", $info);

echo '<h1 class="clr1 gapBelowSmall">ಸಂಪುಟ ' . toKannada(intval($volume)) . ', ಸಂಚಿಕೆ ' . toKannada($dpart) . ' <span style="font-size: 0.85em">' . (($info != '')? '(' . $info .')':'') . '</span></h1>';


if(!(isValidVolume($volume) && isValidPart($part)))
{
	echo '<span class="aFeature clr2">Invalid URL</span>';
	echo '</div> <!-- cd-container -->';
	echo '</div> <!-- cd-scrolling-bg -->';
	echo '</main> <!-- cd-main-content -->';
	include("include_footer.php");

    exit(1);
}

$query = 'select * from article where volume=\'' . $volume . '\' and part=\'' . $part . '\'';

$result = $db->query($query); 
$num_rows = $result ? $result->num_rows : 0;

if($num_rows > 0)
{
	while($row = $result->fetch_assoc())
	{
		$query3 = 'select feat_name from feature where featid=\'' . $row['featid'] . '\'';
		$result3 = $db->query($query3); 
		$row3 = $result3->fetch_assoc();
		$titleid = $row['titleid'];
		$dpart = preg_replace("/^0/", "", $row['part']);
		$dpart = preg_replace("/\-0/", "-", $dpart);
		$sumne = preg_split('/-/' , $row['page']);
		$row['page'] = $sumne[0];
		if($result3){$result3->free();}

		echo '<div class="article">';
		echo ($row3['feat_name'] != '') ? '<div class="gapBelowSmall"><span class="aFeature clr2"><a href="feat.php?feature=' . urlencode($row3['feat_name']) . '&amp;featid=' . $row['featid'] . '">' . $row3['feat_name'] . '</a></span></div>' : '';
		//~ echo '	<span class="aTitle"><a target="_blank" href="bookReader.php?volume=' . $row['volume'] . '&amp;part=' . $row['part'] . '&amp;page=' . $row['page'] . '">' . $row['title'] . '</a></span>';
		//~ DJVU link
		echo '	<span class="aTitle"><a target="_blank" href="../Volumes/djvu/' . $row['volume'] . '/' . $row['part'] . '/index.djvu?djvuopts&amp;page=' . $row['page'] . '.djvu&amp;zoom=page">' . $row['title'] . '</a></span>';
		if($row['authid'] != 0) {

			echo '<br/>	<span class="aAuthor">&nbsp;&nbsp;&mdash;';
			$authids = preg_split('/;/',$row['authid']);
			$authornames = preg_split('/;/',$row['authorname']);
			$a=0;
			foreach ($authids as $aid)
			{
				echo '<a class="delim" href="auth.php?authid=' . $aid . '&amp;author=' . urlencode($authornames[$a]) . '">' . $authornames[$a] . '</a> ';
				$a++;
			}
			
			echo '	</span>';
		}
		echo '<br/><span class="downloadspan"><a target="_blank" href="downloadPdf.php?titleid='.$titleid.'">ಡೌನ್ಲೋಡ್ ಪಿಡಿಎಫ್</a></span>';
		echo '</div>';
	}
}

if($result){$result->free();}
$db->close();

?>
			</div> <!-- cd-container -->
		</div> <!-- cd-scrolling-bg -->
	</main> <!-- cd-main-content -->
<?php include("include_footer.php");?>
