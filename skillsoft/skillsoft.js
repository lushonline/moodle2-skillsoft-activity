 /*
 * @package		mod-skillsoft
 * @author		$Author: martinholden1972@googlemail.com $
 * @version		SVN: $Header: https://moodle2-skillsoft-activity.googlecode.com/svn/branches/dev/skillsoft.js 158 2014-12-02 12:12:07Z martinholden1972@googlemail.com $
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
	var _attoeditor = _window.document.getElementById('id_'+name+'editable');

	
	var _htmlareaexists = !(typeof _htmlarea == "undefined");
	var _textareaexists = !(typeof _textarea == "undefined") && _textarea.type == 'textarea';
	var _tinymceexists =  !(typeof tinyMCE== "undefined");
	var _attoexists = !(typeof _attoeditor == "undefined");
	
	if (_htmlareaexists) {
		//Set the value for HTMLArea
		_htmlarea.setHTML(value);
		return;
	} else if(_tinymceexists) {
		//10-SEPT-2014 - Set the underlying textarea so Moodle validation works
		_textarea.value = value;
		tinyMCE.get('id_'+name).setContent(value);
		return;
	} else if(_attoexists) {
		//10-OCT-2014 - Support Atto Editor
		_attoeditor.innerHTML = value;
		_textarea.value = value;
		return;
	} else if(_textareaexists) {
		_textarea.value = value;
		return;
	}
}