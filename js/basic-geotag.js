(function () {
	var latInput = document.getElementById('lat');
	var lngInput = document.getElementById('lng');
	var initialLat = 51.5194133;
	var initialLng = -0.1291453;
	var initialZoom = 8;
	var zoom = 17;
	var map = null;

	function updateInputs(lat, lng) {
		latInput.value = lat;
		lngInput.value = lng;
	}

	function updateGeoForm(position) {
		var lat = position.coords.latitude;
		var lng = position.coords.longitude;

		map.setView([lat, lng], zoom);
		map.eachLayer(function (layer) {
			layer.setOpacity(1);
		});
		setMarker(lat, lng);
		updateInputs(lat, lng);
	}

	function handleGeoError(error) {
		console.log(error);
	}

	function setMarker(lat, lng) {
		var marker = L.marker([lat, lng]);

		marker.options.draggable = true;

		marker.on('drag', function (event) {
			var marker = event.target;
			var markerPosition = marker.getLatLng();
			map.panTo(new L.LatLng(markerPosition.lat, markerPosition.lng));

			updateInputs(markerPosition.lat, markerPosition.lng);
		});

		marker.addTo(map);
	}

	document.addEventListener('DOMContentLoaded', function () {
		map = L.map('basicgeotagmap')

		var lat = latInput.value;
		var lng = lngInput.value;
		if (lat !== '' && lng !== '') {
			map.setView([lat, lng], zoom);
			setMarker(lat, lng);
		} else {
			map.setView([initialLat, initialLng], initialZoom);
		}

		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		}).addTo(map);

		window.setTimeout(function () {
			map.invalidateSize();
		}, 0);

		var locationButton = document.getElementById('current_location');
		locationButton.addEventListener('click', function () {
			navigator.geolocation.getCurrentPosition(updateGeoForm, handleGeoError, {
				enableHighAccuracy: true,
				maximumAge: 10000
			});
		});
	});
})();
