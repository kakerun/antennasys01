window.___gcfg = {lang: 'ja'};

(function() {
	var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
	po.src = 'https://apis.google.com/js/plusone.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();

function wget(url)
{
	// ����
	var http = null;
	if (window.XMLHttpRequest) {	// Safari, Firefox �Ȃ�
		http = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) {	// IE
		try { http = new ActiveXObject("Msxml2.XMLHTTP"); }	// IE6
		catch (e) {
			try { http = new ActiveXObject("Microsoft.XMLHTTP"); }	// IE5
			catch (e) { return null; }	// Error
		}
	}

	// �����ʐM
	http.open("GET", url, false);
	http.send(null);
	return http.responseText;
}

function ccnt(el,nuf)
{
	//var el = document.activeElement;
	var url = el.href;
	wget("./gocount.php?cf=" + nuf + "&c=" + url);
}

$(document).ready(function(){
    $('.accordion_head').click(function() {
        $(this).next().slideToggle();
    }).next().hide();
	$('.accordion_head').hover(function(){
		$(this).css("cursor","pointer");
	},function(){
		$(this).css("cursor","default");
	});
	$.autopager({autoLoad: true,content: '#helist'});
});