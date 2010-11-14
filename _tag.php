<style>
.tagTable td:nth-child(odd){

}
.tagTable td{
padding:10px 10px 10px 10px;
border:1px solid;
}
</style>
<?php     
require_once("app/Application.class.php");

$dataArray = $Application->GetAggregatedDeals($locationInformation["location"]);
$deals = $dataArray[deals];
//echo $Application->d($deals);
$count = 1;
?>

<div  class=' container'   style='margin:50px 0px 0px 0px;font-size:30px;color:gray;text-align:center'  >
	Tag the Deals
</div>

<div class=' container'   style='margin:50px 0px 0px 0px;border:1px solid gray'  >
	<table>
		<tr>
			<td width=20%   style='border:1px solid gray'  >
				<input name='' id='' type='' value=''>
				<input name='' id='' type='submit' value='go'>
			</td>
			<td   style='border:1px solid gray'  >tag
			</td>
		</tr>
	</table>
	
</div>
<div  class=' container'   style='margin:50px 0px 0px 0px;border:1px solid gray'   >
<table class='tagTable'><?php

foreach($deals as $deal){
	if( $count == 5){
		break;
	};
?>
	<tr>
		<td><?=$count.")&nbsp;&nbsp;"; ?><?=$deal[source_name];    ?>:&nbsp;&nbsp;<?=$deal[title];    ?></td>
		<td>tag</td>
		<td>untag</td>
	</tr>	
<?php
$count++;
}
?>	
</table>
</div>