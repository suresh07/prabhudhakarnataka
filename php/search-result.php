<?php include("include_header.php");?>
<main class="cd-main-content">
        <div class="cd-scrolling-bg cd-color-2">
            <div class="cd-container">
                
<?php

include("connect.php");
require_once("common.php");

if(isset($_GET['author'])){$author = $_GET['author'];}else{$author = '';}
if(isset($_GET['text'])){$text = $_GET['text'];}else{$text = '';}
if(isset($_GET['title'])){$title = $_GET['title'];}else{$title = '';}
if(isset($_GET['featid'])){$featid = $_GET['featid'];}else{$featid = '';}
//~ if(isset($_GET['year1'])){$year1 = $_GET['year1'];}else{$year1 = '';}
//~ if(isset($_GET['year2'])){$year2 = $_GET['year2'];}else{$year2 = '';}

$text = entityReferenceReplace($text);
$author = entityReferenceReplace($author);
$title = entityReferenceReplace($title);
$featid = entityReferenceReplace($featid);
//~ $year1 = entityReferenceReplace($year1);
//~ $year2 = entityReferenceReplace($year2);

$author = preg_replace("/[,\-]+/", " ", $author);
$author = preg_replace("/[\t]+/", " ", $author);
$author = preg_replace("/[ ]+/", " ", $author);
$author = preg_replace("/^ +/", "", $author);
$author = preg_replace("/ +$/", "", $author);
$author = preg_replace("/  /", " ", $author);
$author = preg_replace("/  /", " ", $author);

$title = preg_replace("/[,\-]+/", " ", $title);
$title = preg_replace("/[\t]+/", " ", $title);
$title = preg_replace("/[ ]+/", " ", $title);
$title = preg_replace("/^ +/", "", $title);
$title = preg_replace("/ +$/", "", $title);
$title = preg_replace("/  /", " ", $title);
$title = preg_replace("/  /", " ", $title);

$text = preg_replace("/[,\-]+/", " ", $text);
$text = preg_replace("/[\t]+/", " ", $text);
$text = preg_replace("/[ ]+/", " ", $text);
$text = preg_replace("/^ +/", "", $text);
$text = preg_replace("/ +$/", "", $text);
$text = preg_replace("/  /", " ", $text);
$text = preg_replace("/  /", " ", $text);

if($title=='')
{
    $title='.*';
}
if($author=='')
{
    $author='.*';
}
if($featid=='')
{
    $featid='.*';
}

//~ ($year1 == '') ? $year1 = 1111 : $year1 = $year1;
//~ ($year2 == '') ? $year2 = 9999 : $year2 = $year2;
//~ 
//~ if($year2 < $year1)
//~ {
    //~ $tmp = $year1;
    //~ $year1 = $year2;
    //~ $year2 = $tmp;
//~ }

$authorFilter = '';
$titleFilter = '';

$authors = preg_split("/ /", $author);
$titles = preg_split("/ /", $title);

for($ic=0;$ic<sizeof($authors);$ic++)
{
    $authorFilter .= "and authorname REGEXP '" . $authors[$ic] . "' ";
}
for($ic=0;$ic<sizeof($titles);$ic++)
{
    $titleFilter .= "and title REGEXP '" . $titles[$ic] . "' ";
}

$authorFilter = preg_replace("/^and /", "", $authorFilter);
$titleFilter = preg_replace("/^and /", "", $titleFilter);
$titleFilter = preg_replace("/ $/", "", $titleFilter);

if($text=='')
{
    $query="SELECT * FROM
				(SELECT * FROM
					(SELECT * FROM article WHERE $authorFilter) AS tb1
				WHERE $titleFilter) AS tb2
			WHERE featid REGEXP '$featid' ORDER BY volume, part, page";

}
elseif($text!='')
{
    $text = rtrim($text);
    if(preg_match("/^\"/", $text)) {

        $stext = preg_replace("/\"/", "", $text);
        $dtext = $stext;
        $stext = '"' . $stext . '"';
    }
    elseif(preg_match("/\+/", $text)) {

        $stext = preg_replace("/\+/", " +", $text);
        $dtext = preg_replace("/\+/", "|", $text);
        $stext = '+' . $stext;
    }
    elseif(preg_match("/\|/", $text)) {

        $stext = preg_replace("/\|/", " ", $text);
        $dtext = $text;
    }
    else {

        $stext = $text;
        $dtext = $stext = preg_replace("/ /", "|", $text);
    }
    
    $stext = addslashes($stext);
    
    $query="SELECT * FROM
                (SELECT * FROM
                    (SELECT * FROM
                        (SELECT * FROM
                            (SELECT *, MATCH (text) AGAINST ('$stext' IN BOOLEAN MODE) AS relevance FROM searchtable WHERE MATCH (text) AGAINST ('$stext' IN BOOLEAN MODE) ORDER BY relevance DESC) AS tb1
                        WHERE $authorFilter) AS tb2
                    WHERE $titleFilter) AS tb3
                WHERE featid REGEXP '$featid') AS tb4
            WHERE year between $year1 and $year2 ORDER BY volume, part, cur_page";
}

$result = $db->query($query); 
$num_results = $result ? $result->num_rows : 0;

if ($num_results > 0)
{
    echo '<h1 class="clr1 gapBelowSmall">ಫಲಿತಾಂಶ(ಗಳು) - ' . toKannada(intval($num_results)) . '</h1>';
}

$result = $db->query($query); 
$num_rows = $result ? $result->num_rows : 0;
$id = 0;
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
        $info = '';
        $seriesIssue = '';
		$seriesIssue = getseriesIssue($row['volume'], $row['part']);
		if($row['month'] != '')
		{
			$info = $info . getMonth($row['month']);
		}
		if($row['year'] != '')
		{
			$info = $info . ' <span style="font-size: 0.95em">' . toKannada(intval($row['year'])) . '</span>';
		}
		if($row['maasa'] != '')
		{
			$info = $info . ', ' . $row['maasa'] . '&nbsp;ಮಾಸ';
		}
		if($row['samvatsara'] != '')
		{
			$info = $info . ', ' . $row['samvatsara'] . '&nbsp;ಸಂವತ್ಸರ';
		}
		if($seriesIssue['snum'] != '')
		{
			$info = $info . ', ' . toKannada(intval($seriesIssue['snum'])) . ' ನೆಯ ಸರಣಿ ಸಂಚಿಕೆ';
		}
		$info = preg_replace("/^,/", "", $info);
		$info = preg_replace("/^ /", "", $info);
        $sumne = preg_split('/-/' , $row['page']);
		$row['page'] = $sumne[0];
        if($result3){$result3->free();}

        if ((strcmp($id, $row['titleid'])) != 0) {

            echo ($id == "0") ? '<div class="article">' : '</div><div class="article">';

            echo '  <div class="gapBelowSmall">';
            echo ($row3['feat_name'] != '') ? '     <span class="aFeature clr2"><a href="feat.php?feature=' . urlencode($row3['feat_name']) . '&amp;featid=' . $row['featid'] . '">' . $row3['feat_name'] . '</a></span> | ' : '';
			echo '		<span class="aIssue clr5"><a href="toc.php?vol=' . $row['volume'] . '&amp;part=' . $row['part'] . '">ಸಂಪುಟ ' . toKannada(intval($row['volume'])) . ', ಸಂಚಿಕೆ ' . toKannada($dpart) . ' <span class="font_resize">' . (($info != '')? '(' . $info .')':'') . '</span></a></span>';
            echo '  </div>';
            echo '	<span class="aTitle"><a target="_blank" href="../Volumes/djvu/' . $row['volume'] . '/' . $row['part'] . '/index.djvu?djvuopts&amp;page=' . $row['page'] . '.djvu&amp;zoom=page">' . $row['title'] . '</a></span>';
			//~ DJVU link
			//~ echo '	<span class="aTitle"><a target="_blank" href="../Volumes/' . $row['volume'] . '/' . $row['part'] . '/index.djvu?djvuopts&amp;page=' . $row['page'] . '.djvu&amp;zoom=page">' . $row['title'] . '</a></span><br />';
            if($row['authid'] != 0) {

                echo '  <br /><span class="aAuthor">&nbsp;&mdash;';
                $authids = preg_split('/;/',$row['authid']);
                $authornames = preg_split('/;/',$row['authorname']);
                $a=0;
                foreach ($authids as $aid) {

                    echo '<a class="delim" href="auth.php?authid=' . $aid . '&amp;author=' . urlencode($authornames[$a]) . '">' . $authornames[$a] . '</a> ';
                    $a++;
                }

                echo '  </span>';
            }
            //~ if($text != '')
            //~ {
                //~ echo '<br /><span class="aIssue">Text match found at page(s) : </span>';
                //~ echo '<span class="aIssue"><a href="downloadPdf.php?titleid='.$titleid.'" target="_blank">' . intval($row['cur_page']) . '</a> </span>';
            //~ }
            echo '<br/><span class="downloadspan"><a href="downloadPdf.php?titleid='.$titleid.'" target="_blank">ಡೌನ್ಲೋಡ್ ಪಿಡಿಎಫ್</a> </span>';
            $id = $row['titleid'];
        }
        else 
        {
            if($text != '')
            {
                echo '&nbsp;<span class="aIssue"><a href="downloadPdf.php?titleid='.$titleid.'">' . intval($row['cur_page']) . '</a> </span>';
            }
            $id = $row['titleid'];
        }
    }
}
else
{
    echo '<a href="search.php" class="sml clr2">Sorry! No results. Hit the back button or click here to try again.</a>';
}

if($result){$result->free();}
$db->close();

?>
                </div> <!-- article card -->
            </div> <!-- cd-container -->
        </div> <!-- cd-scrolling-bg -->
    </main> <!-- cd-main-content -->
<?php include("include_footer.php");?>
