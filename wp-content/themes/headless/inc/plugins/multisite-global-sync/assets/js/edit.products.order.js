jQuery(document).ready(function ($) {
  console.log('heu3hiuh4r iwuehruerh');
    var postTable = $('.wp-list-table.posts tbody');

    // Create the HTML for the overlay and spinner
    var overlayHtml = '<div class="cpo-overlay">' +
        '<div class="cpo-spinner"></div>' +
        '</div>';

    // Append the overlay and spinner to the body
    $('body').append(overlayHtml);

    // Sort Button
    const sortButton = '<input type="button" id="sort-products-button" class="button button-primary" style="background-color: #00a32a; border: 1px solid #00a32a;  float: inline-end; margin-top: 5px; margin-right: 5px;" value="Enable Sort Products">';
    $(".tablenav.top .actions:first-child").append(sortButton);


    function getInitialOrder() {
        return postTable.find('tr:not(.inline-edit-row)').map(function (index, element) {
            return {
                id: $(element).attr('id').replace('post-', ''),
                position: parseInt($(element).find('.column-order').text())
            };
        }).get();
    }

    function getChangedItems(initialOrder, newOrder) {
        console.log('getChangedItems initialOrder', initialOrder);
        console.log('getChangedItems newOrder', newOrder);

        const initialOrderMap = new Map(initialOrder.map(item => [item.id, item.position]));
        return newOrder.filter(function (newItem) {
            const initialPosition = initialOrderMap.get(newItem.id);
            return newItem.new_position !== initialPosition;
        });
    }



    function initSortable() {
        var paged = parseInt($('#current-page-selector')[0].value);
        var perPage = parseInt($('#edit_product_per_page')[0].value);
        var offset = (paged - 1) * perPage;

        var initialOrder = getInitialOrder();
        console.log(initialOrder);

        postTable.sortable({
            items: 'tr:not(.inline-edit-row)',
            axis: 'y',
            helper: function (e, ui) {
                return ui;
            },
            start: function (event, ui) {
                ui.item.closest('.wp-list-table').addClass('dragging');
                ui.placeholder.height(ui.item.height());
            },
            update: function (event, ui) {
                var order = postTable.sortable('toArray', { attribute: 'id' });


                var newOrder = order.map(function (postId, index) {
                    return {
                        id: postId.replace('post-', ''),
                        new_position: offset + index
                    };
                });

                var changedItems = getChangedItems(initialOrder, newOrder);

                console.log('changedItems', changedItems);

                $.post(customPostOrder.ajax_url, {
                    action: 'update_menu_order',
                    order: changedItems, // Send only the changed items
                    security: customPostOrder.nonce,
                })

                .done(function (response) {
                    if (response.success) {
                        postTable.find('tr').each(function (index) {
                            $(this).find('.column-order').text((paged - 1) * perPage + index);
                        });
                    }
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    console.error('AJAX request failed:', textStatus, errorThrown);
                })
                .always(function () {
                    // Hide the overlay and spinner after the AJAX request is complete or failed
                    $('.cpo-overlay').hide();
                });

                // Show the overlay and spinner before sending the AJAX request
                $('.cpo-overlay').show();
            },
            stop: function (event, ui) {
                ui.item.closest('.wp-list-table').removeClass('dragging');
            },
        });
    }

    $('#sort-products-button').on('click', function () {
        var isEnabled = $('body').hasClass('product-ordering-enabled');
        var currentUrl = new URL(window.location.href);

        if (!isEnabled) {
            $('body').addClass('product-ordering-enabled');
            $(this).val('Disable Sort Products');
            currentUrl.searchParams.set('product_ordering_enabled', 'true');
            currentUrl.searchParams.set('orderby', 'menu_order');
            currentUrl.searchParams.set('order', 'ASC');
        } else {
            $('body').removeClass('product-ordering-enabled');
            $(this).val('Enable Sort Products');
            currentUrl.searchParams.delete('product_ordering_enabled');
            currentUrl.searchParams.delete('orderby');
            currentUrl.searchParams.delete('order');
        }

        // Redirect to the updated product list
        window.location.href = currentUrl.toString();
    });

    function enableSorting() {
        var currentUrl = new URL(window.location.href);
        var searchParams = currentUrl.searchParams;
        searchParams.set('product_ordering_enabled', 'true');
        searchParams.set('orderby', 'menu_order');
        searchParams.set('order', 'ASC');

        // Remove other URL parameters
        for (var key of searchParams.keys()) {
            if (!['product_ordering_enabled', 'orderby', 'order', 'paged', 'post_type'].includes(key)) {
                searchParams.delete(key);
            }
        }

        // Redirect to the updated product list
        window.location.href = currentUrl.toString();
    }

    function disableSorting() {
        var currentUrl = new URL(window.location.href);
        var searchParams = currentUrl.searchParams;
        searchParams.delete('product_ordering_enabled');
        searchParams.delete('orderby');
        searchParams.delete('order');

        // Redirect to the updated product list
        window.location.href = currentUrl.toString();
    }

    function isSortingEnabled() {
        var currentUrl = new URL(window.location.href);
        return currentUrl.searchParams.get('product_ordering_enabled') === 'true' &&
            currentUrl.searchParams.get('orderby') === 'menu_order' &&
            currentUrl.searchParams.get('order') === 'ASC';
    }

    $('#sort-products-button').on('click', function () {
        if (isSortingEnabled()) {
            disableSorting();
        } else {
            enableSorting();
        }
    });

    if (isSortingEnabled()) {
        $('body').addClass('product-ordering-enabled');
        $('#sort-products-button').val('Disable Sort Products');
        initSortable();
    }



});
