/**
 * crpTag
 *
 * @copyright (c) 2008 Daniele Conca
 * @link http://code.zikula.org/crptag Support and documentation
 * @author Daniele Conca <conca.daniele@gmail.com>
 * @license GNU/GPL - v.2.1
 * @package crpTag
 */
 
function crpTagFormInit(){
	// init
  tlist2 = new FacebookList('crptag-demo', 'crptag-auto');
    
  var pars = "module=crpTag&func=getTags" +
							'&alias=true';
	
	var myAjax = new Ajax.Request("ajax.php", {
		method: 'get',
		parameters: pars,
		onComplete: fetch_tags_response
	});	
}

function fetch_tags_response(req){
	if (req.status != 200) {
		pnshowajaxerror(req.responseText);
		showinfo();
		return;
	}

	var jsonArray = pndejsonize(req.responseText);
	
	for (i in jsonArray) {
		if (isNumeric(i)) {
			tlist2.autoFeed(jsonArray[i]);
		}
	}
	
	update_tagfeed();
	
	Event.observe('additional_input', 'blur', function(){
        update_tagfeed()
    });
  
}

// key verification
function isNumeric(strString)//  check for valid numeric strings
{
	var strValidChars = "0123456789.-";
	var strChar;
	var blnResult = true;
	
	if (strString.length == 0) 
		return false;
	
	//  test strString consists of valid characters listed above
	for (k = 0; k < strString.length && blnResult == true; k++) {
		strChar = strString.charAt(k);
		if (strValidChars.indexOf(strChar) == -1) {
			blnResult = false;
		}
	}
	return blnResult;
}

function update_tagfeed(){
	tlist2.update();
	$('crptag-demo').value=$F('crptag-demo')+','+$F('additional_input');
	Form.Element.focus('crptag-list');
}