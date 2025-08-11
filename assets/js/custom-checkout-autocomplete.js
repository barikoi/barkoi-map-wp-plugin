jQuery(function ($) {
    console.log(bkoimadhk_autocompleteConfig);
    let abortController;

    function fetchAddressSuggestions(query, type) {
        if (abortController) {
            abortController.abort();
        }

        abortController = new AbortController();
        const signal = abortController.signal;
        const endpoint = `https://barikoi.xyz/v2/api/search/autocomplete/place?api_key=${bkoimadhk_autocompleteConfig.apiKey}&q=${encodeURIComponent(query)}`;

        fetch(endpoint, { signal })
            .then(response => response.json())
            .then(data => {
                if (data.status === 200) {
                    updateSuggestionsDropdown(data.places, type);
                }
            })
            .catch(error => {
                if (error.name !== 'AbortError') {
                    console.error('Fetch error:', error);
                }
            });
    }

    function updateSuggestionsDropdown(suggestions, type) {
        const dropdown = $(`#${type}-address-suggestions`);
        dropdown.empty();
        $('#shipping-address-suggestions').show();
        suggestions.forEach(address => {
            $('<div>')
                .addClass('suggestion-item')
                .text(address.address)
                .on('click', function () {
                    dropdown.empty();
                    const addressData = {
                        state: address.district,
                        city: address.city,
                        postcode: address.postCode.toString(),
                        address_1: address.address,
                    };

                    if(window.marker[type]) {
                        window.marker[type].remove();
                    }

                    window.popup[type] = new bkoigl.Popup({ focusAfterOpen: false }).setHTML(
                        `<div style="display: flex; flex-direction: column; align-items: center;">
                           <p>${address.address}</p>
                         </div>`
                    );

                    const lngLat = [address.longitude, address.latitude];

                    const markerIcon = document.createElement("img");
                    markerIcon.src = bkoimadhk_autocompleteConfig.default_marker_icon;
                    markerIcon.style.width = "35px";
                    markerIcon.style.height = "35px";
                    markerIcon.style.cursor = "pointer";

                    window.marker[type] = new bkoigl.Marker({ element: markerIcon })
                        .setLngLat(lngLat)
                        .setPopup(window.popup[type])
                        .addTo(window.maps[type]);

                    window.maps[type].flyTo({
                        center: lngLat,
                        zoom: 10,
                    });

                    window.marker[type].getElement().addEventListener("click", (event) => {
                        event.stopPropagation();
                    }, { once: true });

                    window.marker[type].getElement().addEventListener("mouseenter", () => {
                        window.popup[type].setLngLat(lngLat).addTo(window.maps[type]);
                    });

                    window.marker[type].getElement().addEventListener("mouseleave", () => {
                        window.popup[type].remove();
                    });

                    if (type === 'shipping') {
                        wp.data.dispatch('wc/store/cart').setShippingAddress(addressData);
                    } else {
                        wp.data.dispatch('wc/store/cart').setBillingAddress(addressData);
                    }
                })
                .appendTo(dropdown);
        });
    }

    function initAutocomplete(type) {
        if(bkoimadhk_autocompleteConfig.autocomplete_switch == 1){
            console.log(type);
            const addressField = $(`#${type}-address_1`);
            if (addressField.length) {
                const suggestionsContainer = $('<div>')
                    .attr('id', `${type}-address-suggestions`)
                    .css({
                        border: '1px solid #ccc',
                        maxHeight: '200px',
                        overflowY: 'auto',
                        position: 'absolute',
                        zIndex: 999,
                        backgroundColor: '#fff',
                    })
                    .insertAfter(addressField);

                addressField.on('input', function () {
                    const query = $(this).val();
                    if (query.length > 2) {
                        fetchAddressSuggestions(query, type);
                    } else {
                        suggestionsContainer.empty();
                    }
                });
            }
        }
    }

    $(document).ready(function () {
        function checkFieldsReady() {
            if ($('#shipping-address_1').length > 0 || $('#billing-address_1').length > 0) {
                initAutocomplete('shipping');
            } else {
                setTimeout(checkFieldsReady, 200);
            }
        }

        if (!$('input[type="checkbox"]').is(':checked')) {
            initAutocomplete("billing");
        }

        checkFieldsReady();
    });

    $(document).on('change', 'input[type="checkbox"]', function() {
        if (!$(this).is(':checked') && bkoimadhk_autocompleteConfig.map_switch == 1) {
            initAutocomplete("billing");
        }
    });

    $(document).on('mouseleave', '#shipping-address_1', function() {
        $(document).on('click', function() {
            $('#shipping-address-suggestions').hide();
        });
    });

    $(document).on('mouseleave', '#billing-address_1', function() {
        $(document).on('click', function() {
            $('#billing-address-suggestions').hide();
        });
    });
});
