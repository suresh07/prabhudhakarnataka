#!/usr/bin/perl

$host = $ARGV[0];
$db = $ARGV[1];
$usr = $ARGV[2];
$pwd = $ARGV[3];

use DBI();
@ids=();

open(IN,"<:utf8","prabudhakarnataka.xml") or die "can't open prabudhakarnataka.xml\n";


my $dbh=DBI->connect("DBI:mysql:database=$db;host=$host","$usr","$pwd");

#vnum, number, month, year, title, feature, authid, page, 

$sth11d=$dbh->prepare("DROP TABLE IF EXISTS article");
$sth11d->execute();
$sth11d->finish();

$sth_enc=$dbh->prepare("set names utf8");
$sth_enc->execute();
$sth_enc->finish();

$sth11r=$dbh->prepare("CREATE TABLE article(title varchar(500),
authid varchar(200),
authorname varchar(1000),
featid varchar(10),
page varchar(50), 
volume varchar(3),
part varchar(10),
year varchar(10), 
month varchar(10),
maasa varchar(500),
samvatsara varchar(500),
snum varchar(10),
titleid varchar(100), primary key(titleid)) ENGINE=MyISAM CHARACTER SET utf8 collate utf8_general_ci;");
$sth11r->execute();
$sth11r->finish();

$line = <IN>;

while($line)
{
	if($line =~ /<volume vnum="(.*)">/)
	{
		$volume = $1;
		print $volume . "\n";
	}
	elsif($line =~ /<part pnum="(.*)" snum="(.*)" month="(.*)" year="(.*)" maasa="(.*)" samvatsara="(.*)">/)
	{
		$part = $1;
		$snum = $2;
		$month = $3;
		$year = $4;
		$maasa = $5;
		$samvatsara = $6;
		$count = 0;
		$prev_pages = "";
	}	
	elsif($line =~ /<title>(.*)<\/title>/)
	{
		$title = $1;
	}
	elsif($line =~ /<feature>(.*)<\/feature>/)
	{
		$feature = $1;
		$featid = get_featid($feature);
	}
	elsif($line =~ /<page>(.*)<\/page>/)
	{
		$page = $1;
		if($page eq $prev_pages)
		{
			$count++;
			$id = "prabudhakarnataka" . $volume . "_" . $part . "_" . $page . "_" . $count; 
		}
		else
		{
			$id = "prabudhakarnataka" . $volume . "_" . $part	 . "_" . $page . "_0";
			$count = 0;
		}
		$prev_pages = $page;
	}
	elsif($line =~ /<author type="(.*)" title="(.*)">(.*)<\/author>/)
	{
		$authorname = $3;
		$authids = $authids . ";" . get_authid($authorname);
		$author_name = $author_name . ";" .$authorname;
	}
	elsif($line =~ /<allauthors \/>/)
	{
		$authids = "0";
		$author_name = "";
	}
	elsif($line =~ /<\/entry>/)
	{
		insert_article($title,$authids,$author_name,$featid,$page,$volume,$part,$year,$month,$maasa,$samvatsara,$id,$snum);
		$authids = "";
		$featid = "";
		$author_name = "";
		$id = "";
	}
	$line = <IN>;
}

close(IN);
$dbh->disconnect();

sub insert_article()
{
	my($title,$authids,$author_name,$featid,$page,$volume,$part,$year,$month,$maasa,$samvatsara,$id,$snum) = @_;
	my($sth1);

	$title =~ s/'/\\'/g;
	$authids =~ s/^;//;
	$author_name =~ s/^;//;
	$author_name =~ s/'/\\'/g;
	$maasa =~ s/'/\\'/g;
	$samvatsara =~ s/'/\\'/g;

	
	$sth1=$dbh->prepare("insert into article values('$title','$authids','$author_name','$featid','$page','$volume','$part','$year','$month','$maasa','$samvatsara','$snum','$id')");
	
	$sth1->execute();
	$sth1->finish();
}

sub get_authid()
{
	my($authorname) = @_;
	my($sth,$ref,$authid);

	$authorname =~ s/'/\\'/g;
	
	$sth=$dbh->prepare("select authid from author where authorname='$authorname'");
	$sth->execute();

	my $ref = $sth->fetchrow_hashref();
	$authid = $ref->{'authid'};
	$sth->finish();
	return($authid);
}

sub get_featid()
{
	my($feature) = @_;
	my($sth,$ref,$featid);

	$feature =~ s/'/\\'/g;
	
	$sth=$dbh->prepare("select featid from feature where feat_name='$feature'");
	$sth->execute();

	my $ref = $sth->fetchrow_hashref();
	$featid = $ref->{'featid'};
	$sth->finish();
	return($featid);
}
