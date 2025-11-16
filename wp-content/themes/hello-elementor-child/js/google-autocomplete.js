function initAutocomplete() {
	console.log(document.querySelectorAll('input[name]'));
	const input = document.querySelector('[name="address-1-street_address"]');
	
	if (!input) return;

	const autocomplete = new google.maps.places.Autocomplete(input, {
		componentRestrictions: { country: "au" }, // only for Australia
		fields: ["address_components", "geometry", "formatted_address"],
		types: ["address"],
	});

	autocomplete.addListener("place_changed", () => {
		const place = autocomplete.getPlace();
		if (!place.address_components) return;

		const components = {};
		place.address_components.forEach(c => {
			const type = c.types[0];
			components[type] = c.long_name;
		});

		// Fill individual fields
		/*document.getElementById("street").value = 
			(components.street_number ? components.street_number + " " : "") + (components.route || "");
		document.getElementById("city").value = components.locality || "";
		document.getElementById("state").value = components.administrative_area_level_1 || "";
		document.getElementById("postcode").value = components.postal_code || "";

		// Get Latitude & Longitude
		if (place.geometry && place.geometry.location) {
			document.getElementById("latitude").value = place.geometry.location.lat();
			document.getElementById("longitude").value = place.geometry.location.lng();
			console.log("Lat:", place.geometry.location.lat(), "Lng:", place.geometry.location.lng());
		}*/
	});
}

// Ensure callback initializes autocomplete
if (typeof google !== "undefined" && google.maps && google.maps.places) {
	initAutocomplete();
} else {
	window.initAutocomplete = initAutocomplete;
}

