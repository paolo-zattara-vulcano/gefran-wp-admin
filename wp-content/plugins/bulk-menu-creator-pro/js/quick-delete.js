jQuery(document).ready(function($){

	function renderQuickDeleteButton(){
		$('#menu-to-edit .menu-item').each(function( i, el ){
			if( $(el).find('a.quick-delete').length == 0 ){
				$('<a/>', {
					class: 'quick-delete',
					title: emi_data.bulk_delete_button,
					click: function(e){
						e.preventDefault();
						let $children = $(el).childMenuItems();
						if( $children.length ){
							if( confirm( emi_data.bulk_delete ) ){
								wpNavMenu.removeMenuItem( $(el) );
								$children.each(function( j, child ){
									wpNavMenu.removeMenuItem( $(child) );
								});
							}else{
								wpNavMenu.removeMenuItem( $(el) );
							}
						}else{
							wpNavMenu.removeMenuItem( $(el) );
						}
					},
				})
				.html(`<span class="screen-reader-text">${emi_data.bulk_delete_button}</span>`)
				.insertBefore( $(el).find('.item-controls .item-type') );
			}
		});
	}

	$(document).on( 'menu-item-added', renderQuickDeleteButton );

	renderQuickDeleteButton();
});