 /*
 * @package		mod-skillsoft
 * @author		$Author$
 * @version		SVN: $Header$
 * @copyright	2009-2014 Martin Holden
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function getStartOver() {
	var containerdiv = document.getElementById('restart');
	
	var startover = document.getElementById('startover');
	var attempt = document.getElementById('attempt');
	
	if(startover != undefined) {
		if (startover.checked) {
			attempt.value = startover.value;
			
			//Remove the "restart" message
			containerdiv.innerHTML="";
			
		}
	}
	return attempt.value;
	//return;
}


/* Used by view.php to open new window to the AICC URL */
function openAICCWindow(url,name,options,fullscreen) {
				var startover = getStartOver();
				if (startover != undefined) {
					url = url + "%3fattempt=" + startover;
				}
                var aiccWin = window.open('',name,options);
                 if (fullscreen) {
                       aiccWin.moveTo(0,0);
                       aiccWin.resizeTo(screen.availWidth,screen.availHeight);
                }
                aiccWin.focus();
                aiccWin.location = url;
                return aiccWin;
        }

/* Used by getolsadata to set values in mod_form abstraction of setting data in textareas 
* Needs md5.js
*/
function setTextArea( thewindow, name, value) {
	var _window = thewindow.window;
	var _textarea = _window.document.getElementById('id_'+name);
	var _htmlarea = eval('_window.'+'editor_'+hex_md5(name));

	var _htmlareaexists = !(typeof _htmlarea == "undefined");
	var _textareaexists = _textarea.type == 'textarea';
	var _tinymceexists =  tinyMCE != null;
	
	if (_htmlareaexists) {
		//Set the value for HTMLArea
		_htmlarea.setHTML(value);
		return;
	} else if(_tinymceexists) {
		tinyMCE.get('id_'+name).setContent(value);
		return;
	} else if(_textareaexists) {
		_textarea.value = value;
		return;
	}
}