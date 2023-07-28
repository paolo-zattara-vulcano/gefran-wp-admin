jQuery(document).ready(function($){

	function renderQuickCopyButton(){
		$('#menu-to-edit .menu-item').each(function( i, el ){
			if( $(el).find('a.quick-copy').length == 0 ){
				$('<a/>', {
					class: 'quick-copy',
					title: emi_data.bulk_copy_button,
					click: function(e){
						e.preventDefault();
						var $cloneButton = $(this);
						var $menuItem = $cloneButton.closest('.menu-item');
						var menuItem = $menuItem.get(0);

						if( menuItem.copying ){
							menuItem.copying++;
						}else{
							menuItem.copying = 1;
							$cloneButton.find('.spinner').addClass('is-active');
						}

						wpNavMenu.addItemToMenu(
							{ 0: $menuItem.getItemData() },
							function( menuMarkup ){
								let $afterMenuItem = $menuItem;
								if( $menuItem.childMenuItems().length ){
									$afterMenuItem = $menuItem.childMenuItems().last();
								}
								let $clone = $(menuMarkup).hideAdvancedMenuItemFields().moveHorizontally( $menuItem.menuItemDepth() ).insertAfter( $afterMenuItem ).updateParentMenuItemDBId();
								
								wpNavMenu.refreshKeyboardAccessibility();
								wpNavMenu.refreshAdvancedAccessibility();
								wp.a11y.speak( menus.itemAdded );
								$(document).trigger( 'menu-item-added', [ $(menuMarkup) ] );

								var clone_data = $clone.getItemData();
								$clone.find('.menu-item-settings :is(input,textarea,select)[name^="menu-item-"]').not('[name^="menu-item-db-id["],[name^="menu-item-object-id["]').each(function( i, input ){
									let name_parts = $(input).attr('name').split( '[' + clone_data['menu-item-db-id'] + ']' );
									let original_input_selector = '[name^="' + name_parts[0] + '["]';
									if( name_parts[1] ){
										original_input_selector += '[name$="]' + name_parts[1] + '"]';
									}
									if( $(input).is(':checkbox') || $(input).is(':radio') ){
										original_input_selector += '[value="' + $(input).attr('value') + '"]';
										$(input).prop( 'checked', $menuItem.find( original_input_selector ).prop('checked') );
									}else{
										$(input).val( $menuItem.find( original_input_selector ).val() );
									}
									$(input).trigger('change');
								});
							},
							function(){
								menuItem.copying--;
								if( ! menuItem.copying ){
									$cloneButton.find('.spinner').removeClass('is-active');
								}
							}
						);
					}
				})
				.html(`<span class="screen-reader-text">${emi_data.bulk_copy_button}</span><span class="spinner"></span>`)
				.insertBefore( $(el).find('.item-controls .item-type') );
			}
		});
	}

	$(document).on( 'menu-item-added', renderQuickCopyButton );

	renderQuickCopyButton();
});