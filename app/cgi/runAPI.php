<?php 

require("../Application.class.php");
include('../simplehtmldom/simple_html_dom.php');

ini_set('display_errors',1);
//error_reporting(E_ALL|E_STRICT); 
error_reporting (E_ALL ^ E_NOTICE); 

$dealpageArray = array();

//include('sites/socialbuys.php');
//include('sites/homerun.php');
//include('sites/groupon.php');
include('sites/socialbuy/socialbuy.php');


echo '<pre>';print_r(  $siteArray   );echo '</pre>';  exit;




?>