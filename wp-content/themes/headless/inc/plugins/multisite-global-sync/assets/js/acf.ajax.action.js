jQuery(document).ready(function ($) {
  
  var overlayHtml = '<div class="cpo-overlay">' +
        '<div class="cpo-spinner"></div>' +
        '</div>';

    // Append the overlay and spinner to the body
    $('body').append(overlayHtml);

  $(".custom-button").click(function () {
    $(".custom-button").css('pointer-events', 'none');
    $(".custom-button").addClass('button-disabled');
    $(document.body).css({'cursor' : 'wait'});
    $('.cpo-overlay').show();
    var data = {
      action: $('input[name="multi_global_action"]').val(),
      post_id: $('input[name="post_ID"]').val(),
      nonce: $('input[name="nonce"]').val(),
    };
    $.ajax({
      type: "POST",
      headers: {          
        Accept: "application/json, text/javascript, */*; q=0.01"   
      },
      url: ajaxurl,
      data: data,
      success: function (response) {
        $('.cpo-overlay').hide();
        $(".custom-button").css('pointer-events', 'auto');
        $(".custom-button").removeClass('button-disabled');
        $(document.body).css({'cursor' : 'default'});
        alert(response);
      },
      error: function (e) {
        $('.cpo-overlay').hide();
        $(".custom-button").css('pointer-events', 'auto');
        $(".custom-button").removeClass('button-disabled');
        $(document.body).css({'cursor' : 'default'});
        alert('Errore durante il salvataggio.'.e);
      } 
    });
    
  });

  $(".taxonomy-button").click(function () {
    $('.cpo-overlay').show();
    $(".taxonomy-button").css('pointer-events', 'none');
    $(".taxonomy-button").addClass('button-disabled');
    $(document.body).css({'cursor' : 'wait'});
    var data = {
      action: "taxonomy_ajax_handler",
      nonce: $('input[name="nonce"]').val(),
      tag_ID: $('input[name="tag_ID"]').val(),
      taxonomy: $('input[name="taxonomy"]').val(),
    };
    $.ajax({
      type: "POST",
      headers: {          
        Accept: "application/json, text/javascript, */*; q=0.01"   
      },
      url: ajaxurl,
      data: data,
      success: function (response) {
        $('.cpo-overlay').hide();
        $(".taxonomy-button").css('pointer-events', 'auto');
        $(".taxonomy-button").removeClass('button-disabled');
        $(document.body).css({'cursor' : 'default'});
        alert(response);
      },
      error: function (e) {
        $('.cpo-overlay').hide();
        $(".taxonomy-button").css('pointer-events', 'auto');
        $(".taxonomy-button").removeClass('button-disabled');
        $(document.body).css({'cursor' : 'default'});
        alert('Errore durante il salvataggio della tassonomia. ' . e);
      } 
    });
    
  });
});
