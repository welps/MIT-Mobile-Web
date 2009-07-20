<?php
/**
 * Copyright (c) 2008 Massachusetts Institute of Technology
 * 
 * Licensed under the MIT License
 * Redistributions of files must retain the above copyright notice.
 * 
 */


require_once "../page_builder/page_header.php";
require_once "../../lib/rss_services.php";
require_once "../../config.inc.php";

$News = new News();
$items = $News->get_feed();
$title = $_REQUEST['title'];
$text = explode("\n", $items[$title]['text']);
$paragraphs = array();
foreach($text as $paragraph) {
  if($paragraph) {
    $paragraphs[] = htmlentities($paragraph);
  }
}


$long_date = date("l, F j, Y G:i:s", $items[$title]['unixtime']);

require "$prefix/detail.html";
$page->output();
    
?>