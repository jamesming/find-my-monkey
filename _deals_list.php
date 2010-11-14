<script type="text/javascript" language="Javascript">
		$(document).ready(function() { 
				  $('.buytag').click(function(event) {
				  	
				  	
							$.get("_deals_list_process_referral.php",{
															aggregate_deal_source_id: $(this).attr('aggregate_deal_source_id')
															},
															function(xml) {
																		var message = $(xml).find('data').text();
													 		});
													 		
							
							$('#referTo').attr('action',$(this).attr('deal_url')).submit();

							 
							
			    });	
		});
</script>
<style>
#giveToCharity{
	text-align:right;
	color:green;
	margin-right:22px;
	font-size:16px;
}

.outerbox{
	border-top:1px solid #9C69D6;
	height:auto;
	margin:0px 22px 0px 22px ;
	padding:40px 0px 10px 10px;
}
.tagBox{ 
#background:url(../images/tag.png) no-repeat 664px -0px;
background-size: 28%; 	
height:auto;
border:0px solid gray;
}

.outerboxTable td{
	
}
.innerbox_left{
height:auto;
}
.innerbox_left table{
#background:#CCD1D5;
}
.innerbox_left_textrow{
}
.vendor_address{
color:darkgray;
font-size:10px;
padding-left:13px; 	
}
#priceValueTable td{
font-size:16px	
}
</style>

<?
require_once("app/Application.class.php");

$dataArray = $Application->GetAggregatedDeals($locationInformation["location"]);


$totalDeals = $dataArray[totalDeals];
$paging = $dataArray[paging] ;
$deals = $dataArray[deals];
$count;

		if( $totalDeals > 0 ){  // deals exist for this location

			foreach( $deals as $deal ){?>
				
				<div   class='outerbox'  <?php     
					
					if( $count == 0 ){?>
						
						  style='border:0px'  
     
					<?php	
					};
					
					?> >
					<div   class=' tagBox '  >
						<table class='outerboxTable '       >
							<tr>
								<td  width='67%'    >
									<div   style='font-size:20px'  >
										<?=$deal[title]?>	
									</div>
									
								</td>
								<td   rowspan=2       >
									<div   style='text-align:center;padding:0px 0px 10px 0px;font-size:30px;color:blue;font-weight:bold'  >$<?=(int)$deal[price]?>
									</div>
									<div     style='text-align:center'     >
											<img	class='buytag cursorIsPointer'  
											
											deal_url='<?=$deal[deal_url]?>'
											aggregate_deal_source_id=<?=$deal[aggregate_deal_source_id]?>  
											oldsrc='../images/BuyMe.png'   style='width:120px'  
											oldsrc='http://www.valentinesdayeveryday.com/images/buy-button.jpg'   
											src='../images/buy4.png'   >
									</div>
									
									
									<div   style='margin:20px 0px 20px 0px;background:url(../images/comparisonBackground.png) no-repeat;font-size:17px;height:55px'  >
										
										<table id='priceValueTable'>
											<tr>
												<td width=33%>
														<div   style='text-align:center'  >Value<br>$<?=(int)$deal[value]?>
														</div>
												</td>
												<td width=33%>
														<div   style='text-align:center'  >You Save<br>$<?=(int)$deal[value] - (int)$deal[price];?>
														</div>
												</td>
												<td width=33%>
														<div   style='text-align:center'  >Discount<br>
															
														<?php
														
															if( (int)$deal[price] > 0 ){
																echo round(100 - ((int)$deal[price]/(int)$deal[value] * 100)); 
															};
														 
														 
														 ?>%
															
															
														</div>
												</td>
											</tr>
										</table>

									</div>
									
									<div   style='padding:5px 0px 0px 0px;font-weight: bold; color: red;text-align:center;'  >
										<img      src='../images/timer.jpg'>
										
															<?php
															
															
																$expiresAt = mktime( 0, 0, 0, date("m", $deal["time_added"]), date("d", $deal["time_added"]) + 1, date("Y", $deal["time_added"]) );
																$seconds_left = $expiresAt - time();
									
																	$hours = floor( $seconds_left / 3600 );
																	
																	if( $hours > 24){
																		echo floor($hours/24). " Days, ".   ($hours % 24)   ." Hours, ";
																	}else{
																		echo $hours . " hours, ";	
																	};								
																	
									
																	$minutes = floor( ( $seconds_left - ( 3600 * $hours ) ) / 60 );
																	echo $minutes . " minutes ";
																	
																	
																	/*$seconds = floor( $seconds_left - ( ( 3600 * $hours ) + ( 60 * $minutes ) ) );
																	echo $seconds . " seconds";*/
																	
																	
																	
																	echo "remaining.";
																	
															?>
										
									</div>
								</td>
							</tr>
							<tr>
								<td  >
									<div class='innerbox_left'         >
										
										<table      >
											<tr>
												<td width='40%'>
													<img   style='margin:10px;width:223px'  src='<?=$deal[img_url]?>'>
												</td>
												<td>
													<div   class=' innerbox_left_textrow'  style='font-size:22px'  >
													</div>
													<div    class=' innerbox_left_textrow'     style='padding:5px 10px'  >
														<?=$deal[vendor_name]?>.&nbsp;&nbsp;Source:&nbsp;<?=$deal[source_name]?>
													</div>
													<div   style='padding:5px 10px'  ><?=$deal[address_all]?>								
													</div>
													<div    class=' innerbox_left_textrow'    style='padding:10px 20px 0px 10px; '    >
														<?=addslashes($deal[description])?>
													</div>												
													<script type="text/javascript" language="Javascript">
																$(document).ready(function() { 
																				$('.emailShare').unbind('click').click(function(event) {
																					urLink = "mailto:?body=TEST&subject=test SUBJECT";
																					document.location = urLink;
																			  });			
																			  
																				$('.twitterLink').unbind('click').click(function(event) {
																						var windowName = 'userConsole'; 
																						var popUp = window.open('http://twitter.com/home?status=testing twitter', windowName, 'width=1000, height=700, left=24, top=24, scrollbars, resizable');
																						if (popUp == null || typeof(popUp)=='undefined') { 	
																							alert('Your browser is configured with a pop-up blocker.  In order to tweet, please go to your settings to allow this site to launch pop-up windows.'); 
																						} 
																						else { 	
																							popUp.focus();
																						}
																			  });	
																		  
																});
													</script>
													<div   style='height:20px;margin:20px 0px 20px 10px;'   >
														
														<!--<img	src='../images/facebook.png'   >&nbsp;&nbsp;-->
														<img	 class=' twitterLink' src='../images/twitter.png'  style='cursor:pointer' >&nbsp;&nbsp;
														<img	class='emailShare'  src='../images/email.png'    style='cursor:pointer'   >&nbsp;&nbsp;&nbsp;
														
<fb:like layout="button_count" show_faces="false" href="http://www.drinkmonkey.com?referredby=<?=$referredby;   ?>&random=<?php echo rand(1, 1000000);    ?>" /></fb>
														
														
													</div>	
												</td>
											</tr>
										</table>
										

										
									</div>
								</td>

							</tr>						
							
						</table>
						

					</div>

				</div>
				
				
				<?php  
				$count++;   
				};
			};
			?>
