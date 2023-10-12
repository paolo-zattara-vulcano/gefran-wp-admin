jQuery(document).ready(function($) {

  var backendBaseUrl = window.location.host

  if(document.body.classList.contains('it_IT')){
    var backendUrl = backendBaseUrl + '/it';
  } else if(document.body.classList.contains('de_DE')){
    var backendUrl = backendBaseUrl + '/de';
  } else if(document.body.classList.contains('fr_FR')){
    var backendUrl = backendBaseUrl + '/fr';
  } else if(document.body.classList.contains('es_ES')){
    var backendUrl = backendBaseUrl + '/es';
  } else if(document.body.classList.contains('pt_PT')){
    var backendUrl = backendBaseUrl + '/pt';
  } else if(document.body.classList.contains('zh_CN')){
    var backendUrl = backendBaseUrl + '/ch';
  } else {
    var backendUrl = backendBaseUrl;
  }

  // STAGING ------------
  if(backendBaseUrl === 'gefranstg.kinsta.cloud'){
    if(document.body.classList.contains('it_IT')){
      var frontendUrl = 'stg--gefran-it.netlify.app';
    } else if(document.body.classList.contains('de_DE')){
      var frontendUrl = 'stg--gefran-de.netlify.app';
    } else if(document.body.classList.contains('fr_FR')){
      var frontendUrl = 'stg--gefran-fr.netlify.app';
    } else if(document.body.classList.contains('es_ES')){
      var frontendUrl = 'stg--gefran-es.netlify.app';
    } else if(document.body.classList.contains('pt_PT')){
      var frontendUrl = 'stg--gefran-pt.netlify.app';
    } else if(document.body.classList.contains('zh_CN')){
      var frontendUrl = 'stg--gefran-cn.netlify.app';
    } else {
      var frontendUrl = 'stg--gefran.netlify.app';
    }
  }

  // PRODUCTION ------------
  else if(backendBaseUrl === 'gefran.kinsta.cloud'){
    if(document.body.classList.contains('it_IT')){
      var frontendUrl = 'gefran.it';
    } else if(document.body.classList.contains('de_DE')){
      var frontendUrl = 'gefran.de';
    } else if(document.body.classList.contains('fr_FR')){
      var frontendUrl = 'gefran.fr';
    } else if(document.body.classList.contains('es_ES')){
      var frontendUrl = 'gefran.es';
    } else if(document.body.classList.contains('pt_PT')){
      var frontendUrl = 'gefran.com.br';
    } else if(document.body.classList.contains('zh_CN')){
      var frontendUrl = 'gefran.cn';
    } else {
      var frontendUrl = 'gefran.com';
    }
  }

  // LOCAL DEV ------------
  else{
    var frontendUrl = 'localhost:8000';
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

});
