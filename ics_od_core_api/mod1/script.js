/*
 * $Id: script.js 48432 2011-06-07 09:27:20Z emilieprudhomme $ 
 */

function newForm(elementPath){
	var element = elementPath;	
	var index = element.indexOf('|', 0);
	var name = element.substring(0, index);
	while( index != -1 ){
		element = element.substring(index +1, element.length);
		index = element.indexOf('|', 0);
		if( index != -1 ){
			name += '[' + element.substring(0, index) + ']';
		}else{
			name += '[' + element + ']';
		}
	}
	var content = document.getElementById('content');
	content.innerHTML += '<div>';
	if( elementPath.substring( (elementPath.length - 'parameters|x'.length), (elementPath.length - 2) ) == 'parameters' ){
		content.innerHTML += '<p><input type="hidden" name="' + name + '[name]" value="" /></p>';
		content.innerHTML += '<p><input type="hidden" name="' + name + '[type]" value="enum" /></p>';
		content.innerHTML += '<p><input type="hidden" name="' + name + '[mandatory]" value="" /></p>';
		content.innerHTML += '<p><input type="hidden" name="' + name + '[default]" value="" /></p>';
		content.innerHTML += '<p><input type="hidden" name="' + name + '[description]" value="" /></p>';
	}else{
		content.innerHTML += '<p><input type="hidden" name="' + name + '[value]" value="" /></p>';
		content.innerHTML += '<p><input type="hidden" name="' + name + '[description]" value="" /></p>';
	}
	content.innerHTML += '</div>';
	changeForm(elementPath);
	//return false;
}

function changeForm(elementPath){
	var current = document.getElementById('current');
	current.value = elementPath;
	document.forms.item(0).submit();
	//return false;
}

function deleteElement(elementPath){
	var element = document.getElementById('delete');
	element.value = elementPath;
	document.forms.item(0).submit();
	//return false;
}
