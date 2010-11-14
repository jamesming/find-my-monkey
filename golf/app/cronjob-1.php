<?php
// offer alerts cronjob
require("Application.class.php");
$Application->ProcessQueue();
?>