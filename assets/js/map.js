bkoigl.accessToken = localStorage.getItem("bkoimadhkApiKey");
let map;
let locations = [];
let marker;
let markers = [];
let abortController;
let popup;
document.getElementById('loadingOverlay').style.display = "none";

console.log("Locations:", locations);

initializeMap();

function initializeMap() {
  const loader = document.getElementById("loader");
  if (loader) {
    loader.style.display = "block";
  }

  locations = localStorage.getItem("locations")
    ? JSON.parse(localStorage.getItem("locations"))
    : [];

  map = new bkoigl.Map({
    container: "map",
    center: [90.3563, 23.685],
    zoom: 7
  });

  map.addControl(new bkoigl.FullscreenControl());
  map.addControl(new bkoigl.NavigationControl());
  map.addControl(new bkoigl.ScaleControl());

  map.on("load", () => {
    map.on("click", (e) => {
      getAddressByreverseGEO(e.lngLat.lng, e.lngLat.lat);
    });
    updateMap("", "", "");
  });
}

async function fetchSuggestions() {
  const query = document.getElementById("locationInput").value;
  //if (query.length < 3) return;

  if (abortController) {
    abortController.abort();
  }

  abortController = new AbortController();
  const signal = abortController.signal;

  const url = `https://barikoi.xyz/v2/api/search/autocomplete/place?api_key=${localStorage.getItem("bkoimadhkApiKey")}&q=${query}`;
  const suggestionsList = document.getElementById("suggestions");
  await fetch(url, { signal })
    .then((response) => response.json())
    .then((data) => {
      console.log(data.places);
      suggestionsList.innerHTML = ""; // Clear previous suggestions
      if(data.status == 200){
        data.places.forEach((place) => {
          const option = document.createElement("option");
          option.value = place.address; // This will show in the autocomplete
          option.dataset.lng = place.longitude; // Store longitude in dataset
          option.dataset.lat = place.latitude;
          option.dataset.address = place.address;
          suggestionsList.appendChild(option);
          console.log("ok")
        });
        document.getElementById("msger").style.display = 'none';
      } else {
        document.getElementById("msger").innerHTML = '<div class="alert alert-danger" role="alert">No results found</div>';
        document.getElementById("msger").style.display = 'block';
      }
    })
    .catch((error) => {
      if (error.name === "AbortError") {
        console.log("Fetch aborted");
      } else {
        console.error("Error fetching suggestions:", error);
        document.getElementById("msger").innerHTML = '<div class="alert alert-danger" role="alert">No results found</div>';
        document.getElementById("msger").style.display = 'block';
      }
    });
}

document
  .getElementById("locationInput")
  .addEventListener("input", function (event) {
    const inputValue = event.target.value;
    const selectedOption = document.querySelector(
      `#suggestions option[value="${inputValue}"]`
    );

    if (selectedOption) {
      const lng = selectedOption.dataset.lng;
      const lat = selectedOption.dataset.lat;
      const address = selectedOption.dataset.address;

      updateMap(lng, lat, address);

      if (abortController) {
        abortController.abort();
      }

      map.flyTo({ center: [lng, lat], maxZoom: 7 });
    }
  });

function updateMap(lng, lat, address) {
  if (lng !== "" && lat !== "" && address !== "") {
    const newLocation = {
      id: Date.now().toString(),
      lng: lng,
      lat: lat,
      address: address,
    };
    locations.push(newLocation);
    localStorage.setItem("locations", JSON.stringify(locations));
  }
  let coordinates = [];
  const tableBody = document.getElementById("locationsTableBody");
  tableBody.innerHTML = "";
  locations &&
    locations.forEach((location) => {
      popup = new bkoigl.Popup({ focusAfterOpen: false }).setHTML(
       `<div style="display: flex; flex-direction: column; align-items: center;">
          <p>${location.address}</p>
          <a href="javascript:void(0)" onclick="deleteLocation('${location.id}','${location.lng}','${location.lat}')" id="delete-link" style="text-decoration: none; color: red;">
            <span class="dashicons dashicons-trash" style="font-size: 24px;"></span>
          </a>
        </div>`
      );
      marker = new bkoigl.Marker({ draggable: false })
        .setLngLat([location.lng, location.lat])
        .setPopup(popup)
        .addTo(map);

      marker.getElement().addEventListener("mouseenter", () => {
        popup.setLngLat([location.lng, location.lat]).addTo(map);
      });

      const row = document.createElement("tr");
      row.innerHTML = `
      <td><input type="text" onkeyup="handleInput('${location.id}','${location.lng}','${location.lat}',event)" style="width: 100%;" value="${location.address}" /></td>
      <td>
        <button onclick="deleteLocation('${location.id}','${location.lng}','${location.lat}')">Delete</button>
        <button onclick="popLocation('${location.id}','${location.lng}','${location.lat}')">POP Marker</button>
      </td>
    `;
      tableBody.appendChild(row);
      markers.push(marker);
      document.getElementById("mapJson").value =
        localStorage.getItem("locations");
      const loader = document.getElementById("loader");
      if (loader) {
        loader.style.display = "none";
      }
      const tablecontainer = document.getElementById("table-container");
      if (tablecontainer) {
        tablecontainer.style.display = "table";
      }

      coordinates.push([Number(location.lng),Number(location.lat)]);

      marker.getElement().addEventListener("click", (event) => {
        event.stopPropagation();
        console.log("First click event executed");
      }, { once: true });

    });

    console.log(coordinates)

    if(coordinates.length > 1){
      map.fitBounds(coordinates, {
        maxZoom: 10,
        padding: 60
      });
    }else{
      if(locations[0] && locations[0].lng && locations[0].lat){
          map.flyTo({ center: [locations[0].lng, locations[0].lat], maxZoom: 10 });
      }
    }
}

function handleInput(id, lng, lat, event) {
  document.getElementById('loadingOverlay').style.display = "block";
  setTimeout(() => {
    locations = locations.map((location) => {
      if (location.id === id) {
        return { id, lng, lat, address: event.target.value };
      }
      return location;
    });

    markers.forEach((marker) => {
      if (
        String(marker._lngLat.lng) === String(lng) &&
        String(marker._lngLat.lat) === String(lat)
      ) {
        marker.remove();
      }
    });

    localStorage.setItem("locations", JSON.stringify(locations));
    locations = localStorage.getItem("locations") ? JSON.parse(localStorage.getItem("locations")) : [];
    updateMap("", "", "");
    document.getElementById('loadingOverlay').style.display = "none";
  }, 5000);
}

function deleteLocation(id, lng, lat) {
  console.log("Deleting location with ID:", id);
  locations = locations.filter((location) => location.id !== id);
  localStorage.setItem("locations", JSON.stringify(locations));
  markers.forEach((marker) => {
    if (
      String(marker._lngLat.lng) === String(lng) &&
      String(marker._lngLat.lat) === String(lat)
    ) {
      marker.remove();
    }
  });
  updateMap("", "", "");
  document.getElementById("mapJson").value = localStorage.getItem("locations");
}

async function getAddressByreverseGEO(lng, lat) {
  const url =
    "https://barikoi.xyz/v2/api/search/reverse/geocode?api_key=" +
    localStorage.getItem("bkoimadhkApiKey") +
    "&longitude=" +
    lng +
    "&latitude=" +
    lat;

  await fetch(url)
    .then((response) => response.json())
    .then((data) => {
      updateMap(lng, lat, data.place.address);
      document.getElementById("locationInput").value = data.place.address;
    })
    .catch((error) => {
      console.error("Error fetching reverse geocode:", error);
    });
}

function popLocation(id,lng,lat)
{
  const selectedLocation = locations.filter((location) => location.id == id);
  map.flyTo({ center: [selectedLocation[0].lng, selectedLocation[0].lat], maxZoom: 10 });
  if (popup) { popup.remove() }
  popup = new bkoigl.Popup({ focusAfterOpen: false }).setHTML(
    `<div style="display: flex; flex-direction: column; align-items: center;">
      <p>${selectedLocation[0].address}</p>
      <a href="javascript:void(0)" onclick="deleteLocation('${selectedLocation[0].id}','${selectedLocation[0].lng}','${selectedLocation[0].lat}')" id="delete-link" style="text-decoration: none; color: red;">
        <span class="dashicons dashicons-trash" style="font-size: 24px;"></span> 
      </a>
    </div>`
  );

  popup.setLngLat([selectedLocation[0].lng, selectedLocation[0].lat]).addTo(map);
  console.log(selectedLocation);
}
