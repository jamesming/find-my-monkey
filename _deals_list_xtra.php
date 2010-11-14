									<div     style='float:left;'   >

										<img   style='margin:0px 0px 0px 13px '  
										src="http://maps.google.com/maps/api/staticmap?center=
										<?php  echo $deal['latitude'];   ?>,
										<?php   echo $deal['longitude'];     ?>&zoom=14&size=150x150&maptype=roadmap&markers=color:red|color:red|<?php   echo $deal['latitude'];     ?>,<?php  echo $deal['longitude'];      ?>&sensor=false">	
										<div     class=' vendor_address'    >
											<?=$deal[address]?><br>										
											<?=$deal[city]?>&nbsp;										
											<?=$deal[state]?>&nbsp;									
											<?=$deal[zipcode]?>
										</div>
									</div>
									<div deal_url='<?=$deal[deal_url]?>' aggregate_deal_source_id=<?=$deal[aggregate_deal_source_id]?>   
											class='buytag cursorIsPointer'  
											style='width:228px;
														 float:right;
														 height:150px;
														 border:1px solid gray'  
														 >&nbsp;
									</div>
									<!--									
									<a   style='padding:15px'  href='<?=urlencode  ( $deal[deal_url])?>'><?=$deal[deal_url]?></a>
									-->
									
									
																		<div   style='border:1px solid gray;font-size:20px;display:none'  >
										<?php  echo number_format(round(100 - ($deal['price']/$deal['value'] * 100)));    ?>% off</font>
									</div>