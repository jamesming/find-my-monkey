var popupStatus = 0;

function loadPopup()
{
	if( popupStatus==0 )
	{
		$('#background-popup').css({
			opacity: 0.7
		});
		$('#background-popup').fadeIn('slow');
		$('#popup-container').fadeIn('slow');
		popupStatus = 1;
	}
}

function disablePopup()
{
	if(popupStatus==1)
	{
		$('#background-popup').fadeOut('slow');
		$('#popup-container').fadeOut('slow');
		popupStatus = 0;
	}
}

function centerPopup()
{
	var windowWidth = document.documentElement.clientWidth;
	var windowHeight = document.documentElement.clientHeight;
	var popupHeight = $('#popup-container').height();
	var popupWidth = $('#popup-container').width();
	$('#popup-container').css({
		position: 'absolute',
		top: windowHeight/2-popupHeight/2,
		left: windowWidth/2-popupWidth/2
	});
	
	$('#background-popup').css({
		height: windowHeight
	});
	
}

function DoSharePopup()
{// open share popup
	centerPopup();
	loadPopup();
}

$(document).ready(function(){
			
	$('#popup-close').click(function(){
		disablePopup();
	});

	$('#background-popup').click(function(){
		disablePopup();
	});
	$(document).keypress(function(e){
		if(e.keyCode==27 && popupStatus==1){
			disablePopup();
		}
	});
});