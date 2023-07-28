jQuery(function($){

	function extrasCallback(){
		$('#bulk-menu-pro-checklist input[type=checkbox]:checked').first().prop( 'checked', false );
		if( ! $('#bulk-menu-pro-checklist input[type=checkbox]:checked').length ){
			$('.bulk_menu_pro_extra_items .spinner').removeClass('is-active');
		}else{
			addExtrasToMenu();
		}
	}

	function addExtrasToMenu(){
		let $li = $('#bulk-menu-pro-checklist input[type=checkbox]:checked').first().closest('li');
		wpNavMenu.addLinkToMenu(
			$li.find('input.menu-item-url').val(),
			$li.find('input.menu-item-title').val(),
			false,
			extrasCallback
		);
	}

	$('#submit-extras-bulk-menu-pro').on('click', function(e){
		e.preventDefault();
		$(this).next('.spinner').addClass('is-active');
		addExtrasToMenu();
	});

	function renderExtraItemsFields(){
		// post types
		// taxonomies
		$.each( { posttype: 'posttypes', taxonomy: 'taxonomies' }, function( singular, plural ){
			$('input[value="#emi_' + singular + '"][type=text]').closest('.menu-item-settings').each(function(){
				let key = parseInt( $(this).attr('id').substring( 19 ) );

				$(this).children('p:not(.field-move):not(.field-css-classes)').remove();

				let options = '';
				let selected = '';
				if( typeof emi_data[ plural + '_values' ][ key ] != 'undefined' ){
					selected = emi_data[ plural + '_values' ][ key ][ singular ];
				}
				$.each( emi_data[ plural ], function( value, text ){
					options += `<option value="${value}"${ selected == value ? ' selected' : '' }>${text}</option>`;
				});

				let limit = -1;
				if( typeof emi_data[ plural + '_values' ][ key ] != 'undefined' ){
					limit = emi_data[ plural + '_values' ][ key ]['limit'];
				}

				let exclude = '';
				if( typeof emi_data[ plural + '_values' ][ key ] != 'undefined' ){
					exclude = emi_data[ plural + '_values' ][ key ]['exclude'];
				}

				let levels = -1;
				if( typeof emi_data[ plural + '_values' ][ key ] != 'undefined' ){
					levels = emi_data[ plural + '_values' ][ key ]['levels'];
				}

				let terms_options = '';
				if( singular == 'posttype' ){
					$.each( emi_data.terms_data, function( term_slug, term_data ){
						terms_options += `<optgroup label="${term_data.label}">`;
						$.each( term_data.terms, function( term_id, term_name ){
							let selected = false;
							if( typeof emi_data[ plural + '_values' ][ key ] != 'undefined' ){
								selected = ~$.inArray( term_id, emi_data[ plural + '_values' ][ key ]['terms'] );
							}
							terms_options += `<option value="${term_id}"${ selected ? ' selected' : '' }>${term_name}</option>`;
						});
						terms_options += `</optgroup>`;
					}); 
				}

				let orderby = [];
				if( singular == 'posttype' ){
					orderby = [ 'default', 'ID', 'author', 'title', 'name', 'date', 'modified', 'menu_order' ];
				}else{
					orderby = [ 'default', 'name', 'slug', 'term_group', 'term_id', 'id', 'description', 'parent', 'term_order', 'count' ];
				}
				let orderby_options = '';
				let orderby_selected = '';
				if( typeof emi_data[ plural + '_values' ][ key ] != 'undefined' ){
					orderby_selected = emi_data[ plural + '_values' ][ key ]['orderby'];
				}
				$.each( orderby, function( i, item ){
					orderby_options += `<option value="${item}"${ orderby_selected == item ? ' selected' : '' }>${item}</option>`;
				});

				let order = { default: 'default', ASC: 'ASC (1,2,3)', DESC: 'DESC (3,2,1)' };
				let order_options = '';
				let order_selected = '';
				if( typeof emi_data[ plural + '_values' ][ key ] != 'undefined' ){
					order_selected = emi_data[ plural + '_values' ][ key ]['order'];
				}
				$.each( order, function( value, text ){
					order_options += `<option value="${value}"${ order_selected == value ? ' selected' : '' }>${text}</option>`;
				});

				let showempty = false;
				if( typeof emi_data[ plural + '_values' ][ key ] != 'undefined' ){
					showempty = emi_data[ plural + '_values' ][ key ]['showempty'];
				}

				let showcount = false;
				if( typeof emi_data[ plural + '_values' ][ key ] != 'undefined' ){
					showcount = emi_data[ plural + '_values' ][ key ]['showcount'];
				}
				
				$(this).prepend(`
					<input type="hidden" id="edit-menu-item-title-${key}" name="menu-item-title[${key}]" value="${emi_data[ plural + '_title' ]}">
					<input type="hidden" id="edit-menu-item-url-${key}" name="menu-item-url[${key}]" value="#emi_${singular}">

					<p class="description">
						<label>
							${emi_data[ plural + '_label' ]}
							<select id="edit-menu-item-${singular}-${key}" class="widefat" name="menu-item-${singular}[${key}]">
								${options}
							</select>
						</label>
					</p>

					<p class="description">
						<label>
							${emi_data.limit_label}
							<input type="number" id="edit-menu-item-limit-${key}" class="widefat" name="menu-item-limit[${key}]" value="${limit}">
						</label>
						<small>${emi_data.limit_help}</small>
					</p>

					<p class="description">
						<label>
							${emi_data.exclude_label}
							<input type="text" id="edit-menu-item-exclude-${key}" class="widefat" name="menu-item-exclude[${key}]" value="${exclude}">
						</label>
						<small>${emi_data.exclude_help}</small>
					</p>

					<p class="description">
						<label>
							${emi_data.levels_label}
							<input type="number" id="edit-menu-item-levels-${key}" class="widefat" name="menu-item-levels[${key}]" value="${levels}">
						</label>
						<small>${emi_data.levels_help}</small>
					</p>

					${
						singular != 'posttype' || ! terms_options ? '' : `
							<p class="description">
								<label>
									${emi_data.terms_label}
									<select multiple id="edit-menu-item-terms-${key}" class="widefat" name="menu-item-terms[${key}][]">
										${terms_options}
									</select>
								</label>
								<small>${emi_data.terms_help}</small>
							</p>
						`
					}

					<p class="description">
						<label>
							${emi_data.orderby_label}
							<select id="edit-menu-item-orderby-${key}" class="widefat" name="menu-item-orderby[${key}]">
								${orderby_options}
							</select>
						</label>
					</p>

					<p class="description">
						<label>
							${emi_data.order_label}
							<select id="edit-menu-item-order-${key}" class="widefat" name="menu-item-order[${key}]">
								${order_options}
							</select>
						</label>
					</p>

					${ singular == 'taxonomy' ? `
						<p class="description">
							<input type="hidden" name="menu-item-showempty[${key}]" value="0">
							<label>
								<input type="checkbox" id="edit-menu-item-showempty-${key}" name="menu-item-showempty[${key}]" value="1" ${ showempty ? 'checked' : '' }> ${emi_data.showempty_label}
							</label>
							<br><small>${emi_data.showempty_help}</small>
						</p>

						<p class="description">
							<input type="hidden" name="menu-item-showcount[${key}]" value="0">
							<label>
								<input type="checkbox" id="edit-menu-item-showcount-${key}" name="menu-item-showcount[${key}]" value="1" ${ showcount ? 'checked' : '' }> ${emi_data.showcount_label}
							</label>
							<br><small>${emi_data.showcount_help}</small>
						</p>` : ''
					}
				`);
			});
		});

		// login logout
		$('input[value="#emi_loginout"][type=text]').closest('.menu-item-settings').each(function(){
			let key = parseInt( $(this).attr('id').substring( 19 ) );

			$(this).children('p:not(.field-move):not(.field-css-classes)').remove();

			let login_label = 'Login';
			let login_url = '';
			let login_redirect_url = '';

			let logout_label = 'Logout';
			let logout_redirect_url = '';
			
			if( typeof emi_data.loginout_values[ key ] != 'undefined' ){
				login_url = emi_data.loginout_values[ key ]['login_url'];
				login_redirect_url = emi_data.loginout_values[ key ]['login_redirect_url'];
				login_label = emi_data.loginout_values[ key ]['login_label'];
			
				logout_redirect_url = emi_data.loginout_values[ key ]['logout_redirect_url'];
				logout_label = emi_data.loginout_values[ key ]['logout_label'];
			}

			$(this).prepend(`
				<input type="hidden" id="edit-menu-item-title-${key}" name="menu-item-title[${key}]" value="${emi_data.loginout_title}">
				<input type="hidden" id="edit-menu-item-url-${key}" name="menu-item-url[${key}]" value="#emi_loginout">

				<p class="description">
					<label>
						${emi_data.login_url_label}
						<input type="text" id="edit-menu-item-login-url-${key}" class="widefat" name="menu-item-login-url[${key}]" value="${login_url}">
						<small>${emi_data.login_url_help}</small>
					</label>
				</p>

				<p class="description">
					<label>
						${emi_data.login_redirect_url_label}
						<input type="text" id="edit-menu-item-login-redirect-url-${key}" class="widefat" name="menu-item-login-redirect-url[${key}]" value="${login_redirect_url}">
						<small>${emi_data.login_redirect_url_help}</small>
					</label>
				</p>

				<p class="description">
					<label>
						${emi_data.login_label}
						<input type="text" id="edit-menu-item-login-${key}" class="widefat" name="menu-item-login[${key}]" value="${login_label}">
						<small>${emi_data.login_help}</small>
					</label>
				</p>

				<br>

				<p class="description">
					<label>
						${emi_data.logout_redirect_url_label}
						<input type="text" id="edit-menu-item-logout-redirect-url-${key}" class="widefat" name="menu-item-logout-redirect-url[${key}]" value="${logout_redirect_url}">
						<small>${emi_data.logout_redirect_url_help}</small>
					</label>
				</p>

				<p class="description">
					<label>
						${emi_data.logout_label}
						<input type="text" id="edit-menu-item-logout-${key}" class="widefat" name="menu-item-logout[${key}]" value="${logout_label}">
						<small>${emi_data.logout_help}</small>
					</label>
				</p>
			`);
		});

		// profile
		$('input[value="#emi_profile"][type=text]').closest('.menu-item-settings').each(function(){
			let key = parseInt( $(this).attr('id').substring( 19 ) );

			$(this).children('p:not(.field-move):not(.field-css-classes)').remove();

			let link = 'author_posts_url';
			let label = '{display_name}';
			if( typeof emi_data.profile_values[ key ] != 'undefined' ){
				if( emi_data.profile_values[ key ]['link'] ){
					link = emi_data.profile_values[ key ]['link'];
				}
				if( emi_data.profile_values[ key ]['label'] ){
					label = emi_data.profile_values[ key ]['label'];
				}
			}

			let options = '';
			$.each( emi_data.profile_link, function( value, text ){
				options += `<option value="${value}"${ link == value ? ' selected' : '' }>${text}</option>`;
			});

			$(this).prepend(`
				<input type="hidden" id="edit-menu-item-title-${key}" name="menu-item-title[${key}]" value="${emi_data.profile_title}">
				<input type="hidden" id="edit-menu-item-url-${key}" name="menu-item-url[${key}]" value="#emi_profile">
				
				<p class="description">
					<label>
						${emi_data.profile_link_label}
						<select id="edit-menu-item-profile-link-${key}" class="widefat" name="menu-item-profile-link[${key}]">
							${options}
						</select>
					</label>
				</p>

				<p class="description">
					<label>
						${emi_data.profile_label}
						<input type="text" id="edit-menu-item-profile-${key}" class="widefat" name="menu-item-profile[${key}]" value="${label}">
					</label>
					<small>${emi_data.profile_help}</small>
				</p>
			`);
		});
	}

	$(document).on('menu-item-added', function(){
		renderExtraItemsFields();
	});

	$('#update-nav-menu').on('click', function(e){
		if( e.target && e.target.className && -1 != e.target.className.indexOf('item-edit') ){
			renderExtraItemsFields();
		}
	});
});