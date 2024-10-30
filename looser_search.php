<?php
/*
Plugin Name: Looser Search Plugin
Plugin URI: http://www.BlogsEye.com/
Description: Replaces the built-in wordpress search with a search more likely to find something even if there are mispellings or terms not found
Version: 1.0 
Author: Keith P. Graham
Author URI: http://www.BlogsEye.com/

This software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/************************************************************
* 	kpg_looser_search_fixup()
*	Does the dirty deed.
*   replaces the where clause in the search with a search that counts the occurences of strings in title and content
*************************************************************/
function kpg_looser_search_fixup($where) {
	 if( is_search()===false ) return $where;
	global $wpdb;
	global $wp_query;
		$wp_query->query_vars['s_kpg']=''; // because it's there

	// alter the where 
	// default uses a Like '%this%', but I'd like to count the stuff
	
	// there are lots of ways to strip out the stuff, 
	// but what I'll do is get rid of the search query and build it from $wp_query class.
	$q=$wp_query->query_vars['s'];
	$q=urldecode($q); // ehcoded?
	// fix up the query string. I 
	// get rid of some punctuation
	$q=str_replace('_',' ',$q); 
	$q=str_replace('.',' ',$q); 
	$q=str_replace('-',' ',$q); 
	$q=str_replace('+',' ',$q); 
	$q=str_replace('"',' ',$q); 
	$q=str_replace("\'",' ',$q); 
	$q=str_replace('`',' ',$q); 
	$q=str_replace('  ',' ',$q);
	$q=str_replace('  ',' ',$q);
	$q=trim($q);
	// put it into an array
	$qs=explode(' ',$q);
	// get rid of common words - don'd need to search for these:
$common=" 0 1 2 3 4 5 6 7 8 9 10 a able about act add after again air all also am an and animal answer any are as ask at back bad be been before being between big boy build but by call came can case cause change child city close come company could country cover cross day did differ different do does don't down draw each early earth end even every eye fact far farm father feel few find first follow food for form found four from get give go good government great group grow had hand hard has have he head help her here high him his home hot house how i if important in into is it its just keep kind know land large last late learn leave left let life light like line little live long look low made make man many may me mean men might more most mother move mr mrs much must my name near need never new next night no north not now number of off office old on one only or other our out over own page part people person picture place plant play point port press problem public put read real right round run said same saw say school sea see seem self sentence set she should show side small so some sound spell stand start state still story study such sun take tell than that the their them then there these they thing think this thought three through time to too tree try turn two under up upon us use very want was water way we week well went were what when where which while who why will with woman word work world would write year you young your ";
	for ($j=0;$j<count($qs);$j++) {
		if (strpos($common,' '.$qs[$j].' ')!==false) {
			unset($qs[$j]);
		}
	}
	if (count($qs)==0) return $where;
	$ptab=$wpdb->posts;
	$where= "AND ((";
	$sql="";
	for ($j=0;$j<count($qs);$j++) {
		$sss=strtolower(mysql_real_escape_string($qs[$j]));
		$sl=strlen($sss);
		if ($sl>2) {
			$sql.="((LENGTH($ptab.post_content) - LENGTH(REPLACE(LCASE($ptab.post_content), '$sss', '')))/$sl) + ";
			$sql.="((LENGTH($ptab.post_title) - LENGTH(REPLACE(LCASE($ptab.post_title), '$sss', '')))*3/$sl) + ";
		}	
	}
	$sql.="0";
	// finish up with the paperwork:
	$where=$where.$sql.")>0) AND $ptab.post_type != 'revision' AND ($ptab.post_status = 'publish' OR $ptab.post_author = 1 AND $ptab.post_status = 'private')";		
	// need to save the $sql variable for use by the orderby variable
	$wp_query->query_vars['s_kpg']=$sql; // because it's there

  return $where;
}

function kpg_looser_search_orderby($orderby) {
	if( is_search()===false ) return $orderby;
	global $wp_query;
	$sql=$wp_query->query_vars['s_kpg']; // must be a better way to do it, but it's late and I want to finish.
	if($sql==null||$sql=='') return $orderby;
	$orderby=" $sql DESC, POST_DATE DESC ";
	return $orderby;
}

  // Plugin added to Wordpress plugin architecture
	add_filter('posts_where', 'kpg_looser_search_fixup' );
	add_filter('posts_orderby', 'kpg_looser_search_orderby' );

	 
?>