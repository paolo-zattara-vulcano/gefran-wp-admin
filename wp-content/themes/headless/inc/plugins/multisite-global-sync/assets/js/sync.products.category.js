jQuery(document).ready(function ($) {

  /*
    // Create the HTML for the overlay and spinner
    var overlayHtml = '<div class="cpo-overlay">' +
        '<div class="cpo-spinner"></div>' +
        '</div>';

    // Append the overlay and spinner to the body
    $('body').append(overlayHtml);
*/
    // Sort Button
    const sortButton = '<input type="button" id="sync-prods-cat-button" class="button button-primary" style="float: inline-end; margin-top: 5px;" value="Sync categories">';
    $(".tablenav.top .actions:first-child").append(sortButton);


    $("#sync-prods-cat-button").click(function () {
        $('.cpo-overlay').show();
        $("#sync-prods-cat-button").css('pointer-events', 'none');
        $("#sync-prods-cat-button").addClass('button-disabled');
        $(document.body).css({'cursor' : 'wait'});
        $.ajax({
          type: "POST",
          headers: {          
            Accept: "application/json, text/javascript, */*; q=0.01"   
          },
          url: syncProductCategory.ajaxurl,
          data: {
              'action': 'products_category_update',
              'nonce' : syncProductCategory.nonce
          },
          success: function (response) {
            $('.cpo-overlay').hide();
            $("#sync-prods-cat-button").css('pointer-events', 'auto');
            $("#sync-prods-cat-button").removeClass('button-disabled');
            $(document.body).css({'cursor' : 'default'});
            alert(response);
          },
          error: function (e) {
            $('.cpo-overlay').hide();
            $("#sync-prods-cat-button").css('pointer-events', 'auto');
            $("#sync-prods-cat-button").removeClass('button-disabled');
            $(document.body).css({'cursor' : 'default'});
            alert('Errore durante il salvataggio della tassonomia. ' . e);
          } 
        });
        
      });



});
