/*
 * $Id: script.js 48432 2011-06-07 09:27:20Z emilieprudhomme $ 
 */

/**
 * Control input form from add application
 * @return true/false
 */
function valid_application_form(){
	// Verifies that the name is filled
	if(document.getElementById('name').value == ""){
		alert('Veuillez saisir le nom.');
		return false;
	}
	// Verifies that the description is filled
	if(document.getElementById('description').value == ""){
		alert('Veuillez saisir la description.');
		return false;
	}
	// Verifies that the cgu is checked
	if(document.getElementById('cgu').checked == false){
		alert('Veuillez accepter les conditions générales d\'utilisation.');
		return false;
	}
	return true;
}

function valid_data_form(){
	// Verifies that the cgu is checked
	if(document.getElementById('cgu').checked == false){
		alert('Veuillez accepter les conditions générales d\'utilisation.');
		return false;
	}
	return true;
}