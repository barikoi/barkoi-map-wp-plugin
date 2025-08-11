jQuery(function ($) {
    window.maps = {}; // Object to store map instances
    window.popup = {};
    window.marker = {};
    let centerlngLat = [90.4125, 23.8103];

    bkoigl.accessToken = bkoimadhk_reverseGeoConfig.apiKey;

    function getAddressByreverseGEO(lng, lat, type) {
        const url = "https://barikoi.xyz/v2/api/search/reverse/geocode?api_key=" + bkoimadhk_reverseGeoConfig.apiKey + "&longitude=" + lng + "&latitude=" + lat + "&district=true&post_code=true";
        fetch(url).then((response) => response.json())
        .then((data) => {

            if (marker[type]) {
                marker[type].remove();
            }

            popup[type] = new bkoigl.Popup({ focusAfterOpen: false }).setHTML(
                `<div style="display: flex; flex-direction: column; align-items: center;">
                    <p>${data.place.address}</p>
                </div>`
            );

            const lngLat = [lng, lat];

            const markerIcon = document.createElement("img");
            markerIcon.src = bkoimadhk_autocompleteConfig.default_marker_icon;
            markerIcon.style.width = "35px";
            markerIcon.style.height = "35px";
            markerIcon.style.cursor = "pointer";

            marker[type] = new bkoigl.Marker({ draggable: false, element: markerIcon })
                .setLngLat(lngLat)
                .setPopup(popup[type]) // Add Popup to Marker
                .addTo(maps[type]);

            const addressData = {
                state: data.place.district,
                city: data.place.city,
                postcode: data.place.postCode.toString(),
                address_1: data.place.address,
            };

            marker[type].getElement().addEventListener("click", (event) => {
                event.stopPropagation();
            }, { once: true });

            marker[type].getElement().addEventListener("mouseenter", () => {
                popup[type].setLngLat([lng, lat]).addTo(maps[type]);
            });

            marker[type].getElement().addEventListener("mouseleave", () => {
                popup[type].remove();
            });

            if (type === 'shipping') {
                wp.data.dispatch('wc/store/cart').setShippingAddress(addressData);
            } else {
                wp.data.dispatch('wc/store/cart').setBillingAddress(addressData);
            }

        })
        .catch((error) => {
            console.error("Error fetching reverse geocode:", error);
        });
    }

    function initializeMap(type) {
        const addressData = {
            state: '',
            city: '',
            postcode: '',
            address_1: '',
        };
        wp.data.dispatch('wc/store/cart').setShippingAddress(addressData);
        wp.data.dispatch('wc/store/cart').setBillingAddress(addressData);

        const mapStyles = {
            Dark: "https://map.barikoi.com/styles/barikoi-dark-mode/style.json",
            Bangla: "https://map.barikoi.com/styles/barikoi-bangla/style.json",
            Light: "https://map.barikoi.com/styles/planet_barikoi_v2/style.json",
        };

        // Select the map style or default to 'Light'
        const selectedMapStyle = mapStyles[bkoimadhk_autocompleteConfig.default_map_style] || mapStyles.Light;

        if (bkoimadhk_autocompleteConfig.default_coordinates != '') {
            centerlngLat = bkoimadhk_autocompleteConfig.default_coordinates.split(',');
        } else {
            getGeoLocationFromBrowser().then((positions) => {
                console.log(`Positions: ${positions}`);
                if (typeof positions === "string") {
                    centerlngLat = positions.split(',');
                } else {
                    centerlngLat = positions;
                }
            });
        }

        console.log(centerlngLat);

        maps[type] = new bkoigl.Map({
            container: `${type}-map`,
            center: centerlngLat,
            zoom: bkoimadhk_autocompleteConfig.default_zoom_level,
            style: selectedMapStyle
        });

        maps[type].addControl(new bkoigl.FullscreenControl());
        maps[type].addControl(new bkoigl.NavigationControl());
        maps[type].addControl(new bkoigl.ScaleControl());

        maps[type].on("load", () => {
            maps[type].on("click", (e) => {
                getAddressByreverseGEO(e.lngLat.lng, e.lngLat.lat, type);
            });
        });
    }

    $(document).on('change', 'input[type="checkbox"]', function() {
        if ($(this).is(':checked')) {
            $('#billing-map-container').hide(); // Hide the map
        } else {
            $('#billing-map-container').show(); // Show the map
            initializeMap('billing');
        }
    });

    function getGeoLocationFromBrowser() {
        return new Promise((resolve, reject) => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;
                        console.log(`Latitude: ${latitude}, Longitude: ${longitude}`);
                        resolve(`${longitude},${latitude}`);
                    },
                    (error) => {
                        console.error(`Error (${error.code}): ${error.message}`);
                        resolve("90.4125,23.8103"); // Fallback to default
                    }
                );
            } else {
                console.error("Geolocation is not supported by this browser.");
                resolve("90.4125,23.8103"); // Fallback to default
            }
        });
    }

    $(document).ready(function () {
        if (bkoimadhk_autocompleteConfig.map_switch == "1") {
            setTimeout(function () {
                $('#shipping-fields').append(`
                    <div id="billing-map-container" style="display:none">
                        <div style="margin-bottom: 5px;padding: 20px 0">
                            <p>Select billing address from here</p>
                        </div>
                        <div id="billing-map" style="width: 100%;height: 200px;padding: 20px 0;"></div>
                    </div>
                `);

                $('#contact-fields').append(`
                    <div style="margin-bottom: 5px;padding: 20px 0">
                        <p>Select Shipping address from here</p>
                    </div>
                    <div id="shipping-map" style="width: 100%;height: 200px;padding: 20px 0;"></div>
                `);

                initializeMap("shipping");

                if (!$('input[type="checkbox"]').is(':checked')) {
                    $('#billing-map-container').show();
                    initializeMap('billing');
                }
            }, 2000);
        }
    });
});
