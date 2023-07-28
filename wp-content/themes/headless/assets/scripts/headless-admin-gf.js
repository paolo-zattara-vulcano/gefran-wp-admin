jQuery(document).ready(function($) {

  var backendBaseUrl = window.location.host

  // STAGING ------------
  if(backendBaseUrl === 'gefranstg.kinsta.cloud'){
    if(document.body.classList.contains('it_IT')){
      var frontendUrl = 'stg.gefran.com';
      var backendUrl = backendBaseUrl + '/it';
    } else if(document.body.classList.contains('de_DE')){
      var frontendUrl = 'stg.gefran.com';
      var backendUrl = backendBaseUrl + '/de';
    } else if(document.body.classList.contains('fr_FR')){
      var frontendUrl = 'stg.gefran.com';
      var backendUrl = backendBaseUrl + '/fr';
    } else if(document.body.classList.contains('es_ES')){
      var frontendUrl = 'stg.gefran.com';
      var backendUrl = backendBaseUrl + '/es';
    } else if(document.body.classList.contains('pt_PT')){
      var frontendUrl = 'stg.gefran.com';
      var backendUrl = backendBaseUrl + '/pt';
    } else if(document.body.classList.contains('zh_CN')){
      var frontendUrl = 'stg.gefran.com';
      var backendUrl = backendBaseUrl + '/ch';
    } else {
      var frontendUrl = 'stg.gefran.com';
      var backendUrl = backendBaseUrl;
    }
    // const backendUrl = 'gefran-admin.dvl';

    // PRODUCTION ------------
  } else {
    if(document.body.classList.contains('it_IT')){
      var frontendUrl = 'gefran.it';
      var backendUrl = backendBaseUrl + '/it';
    } else if(document.body.classList.contains('de_DE')){
      var frontendUrl = 'gefran.de';
      var backendUrl = backendBaseUrl + '/de';
    } else if(document.body.classList.contains('fr_FR')){
      var frontendUrl = 'gefran.fr';
      var backendUrl = backendBaseUrl + '/fr';
    } else if(document.body.classList.contains('es_ES')){
      var frontendUrl = 'gefran.es';
      var backendUrl = backendBaseUrl + '/es';
    } else if(document.body.classList.contains('pt_PT')){
      var frontendUrl = 'gefran.com.br';
      var backendUrl = backendBaseUrl + '/pt';
    } else if(document.body.classList.contains('zh_CN')){
      var frontendUrl = 'gefran.cn';
      var backendUrl = backendBaseUrl + '/ch';
    } else {
      var frontendUrl = 'gefran.com';
      var backendUrl = backendBaseUrl;
    }
    // const backendUrl = 'gefran-admin.dvl';
  }


  // Single post link
  let frontendLink = document.querySelector('#sample-permalink');
  if(frontendLink){

    if(frontendLink.querySelector('a')){
      var thePostUrlElem = frontendLink.querySelector('a');
    } else if(frontendLink.href){
      var thePostUrlElem = frontendLink;
    }

    let thePostHref = thePostUrlElem.href.replace(backendUrl, frontendUrl);

    var newButton = '<a href="' + thePostHref + '" target="_blank" class="button button-small hide-if-no-js" aria-label="Visualizza Frontend">Visualizza</button>';
    $('#edit-slug-box').append(newButton);
  }


  // All posts links
  let postFilter = document.querySelector('#posts-filter');
  if(postFilter){

    var thePostUrlElem = document.querySelectorAll('#the-list .row-actions .view a');

    thePostUrlElem.forEach((elem) => {

      let thePostHref = elem.href.replace(backendUrl, frontendUrl);
      var newButton = '<a href="' + thePostHref + '" target="_blank" class="button button-small hide-if-no-js" aria-label="Visualizza Frontend" style="line-height: 18px; height: 19px; min-height: 0; margin-left: 10px;">Visualizza</button>';
      $(elem).closest('.row-actions').append(newButton);
      $(elem).closest('.view').hide();
    });

  }

	// //By Enrico
	// var timer;
  //
	// //Esegue sincronizzazione quando viene fatta una modifica all'albero delle cartelle
	// jQuery(document).on('DOMSubtreeModified','#fbj', function(){
	// 	if (timer) clearTimeout(timer);
	// 	timer = setTimeout(function() {
	// 		var id_blog=jQuery("input[name=id_blog]").val();
	// 		jQuery.ajax({
	// 			url: "/sincronizzazione.php",
	// 			type: 'post',
	// 			data: {id_blog: id_blog},
	// 			dataType: "json",
	// 			success: function(response){
	// 			}
	// 		});
	// 	},1000); //Cambiare tempo con un tempo minore (1 o 2 secondi) se sito molto veloce (ora sono 10 secondi)
	// });

});
