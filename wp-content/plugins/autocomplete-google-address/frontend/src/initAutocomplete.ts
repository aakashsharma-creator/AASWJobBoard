import { initializeMap } from "./mapHandler";
import { AddressConfiguration } from "./types/index";
import { generateOptions } from "./utils/generateOptions";
import { getInputByID } from "./utils/getInputByID";
import { processAddressComponents } from "./utils/processAddressComponents";

const { isPlan } = window.gapFrontendData;

export async function initAutocomplete(
  input: AddressConfiguration,
  countryList: any,
  defaultMapContainerId: string
) {
  // let search_type: string | string[] = input.search_type;
  // if (typeof search_type === "string") {
  //   search_type = [search_type];
  // }
  console.log("front end search", input.search_type);
  console.log("Country list", countryList);

  const options = generateOptions(input.search_type, countryList);

  const inputElement = getInputByID(input.street_address_id);

  // Early return if inputElement is null or undefined
  if (!inputElement) {
    console.warn(`Input element with ID ${input.street_address_id} not found.`);
    return;
  }

  // Determine the map container
  let mapContainer: HTMLElement | null;
  if (input.map_display_id && input.map_display_id !== "parent") {
    // Use the provided class name for map_display_id
    const mapDisplayElements = document.getElementsByClassName(
      input.map_display_id
    );
    if (mapDisplayElements.length > 0) {
      const mapDisplayElement = mapDisplayElements[0] as HTMLElement;

      // Check if the map container exists within the class
      mapContainer = mapDisplayElement.querySelector(
        `#${defaultMapContainerId}`
      ) as HTMLElement;

      if (!mapContainer) {
        // Create a new map container if it doesn't exist
        mapContainer = document.createElement("div");
        mapContainer.id = defaultMapContainerId;
        mapContainer.style.width = `${input.map_width}%`;
        mapContainer.style.height = `${input.map_height}px`;

        // Append the new map container to the mapDisplayElement
        mapDisplayElement.appendChild(mapContainer);
      }
    } else {
      console.error(
        `No element found with class name ${input.map_display_id}. Cannot display map.`
      );
      return;
    }
  } else {
    // Default to creating a map container next to the input field
    mapContainer = document.getElementById(defaultMapContainerId);
    if (!mapContainer) {
      // Create a new map container
      mapContainer = document.createElement("div");
      mapContainer.id = defaultMapContainerId;
      mapContainer.style.width = `${input.map_width}%`;
      mapContainer.style.height = `${input.map_height}px`;

      // Append the map container to the input's parent
      if (inputElement.parentElement) {
        inputElement.parentElement.appendChild(mapContainer);
      } else {
        console.error(
          `Parent element for input ID ${input.street_address_id} not found.`
        );
        return;
      }
    }
  }

  // Check if show_map is true
  if (input.show_map && isPlan) {
    // Initialize the map
    const mapData = await initializeMap(
      mapContainer.id,
      Number(input.latitude),
      Number(input.longitude),
      input
    );
    if (!mapData) return;

    const { map, marker } = mapData;

    const autocomplete = new google.maps.places.Autocomplete(
      inputElement,
      options
    );

    autocomplete.addListener("place_changed", () => {
      const place = autocomplete.getPlace();
      console.log(place);

      if (!place.geometry || !place.address_components) {
        console.log(
          "Autocomplete's returned place contains no geometry or address components"
        );
        return;
      }

      // Update the map and marker position
      const location = place.geometry.location;
      if (location) {
        map.setCenter(location);
        marker.position = location; // Update marker position
      }

      // Auto-fill form fields using processAddressComponents
      if (isPlan) {
        processAddressComponents(place, input);
      }
    });
  } else {
    // If show_map is false, hide the map container
    if (mapContainer) {
      mapContainer.style.display = "none"; // Hide the map container
    }

    // Initialize only autocomplete without the map
    const autocomplete = new google.maps.places.Autocomplete(
      inputElement,
      options
    );

    autocomplete.addListener("place_changed", () => {
      const place = autocomplete.getPlace();
      console.log(place);

      if (!place.geometry || !place.address_components) {
        console.log(
          "Autocomplete's returned place contains no geometry or address components"
        );
        return;
      }

      // Auto-fill form fields using processAddressComponents
      if (isPlan) {
        processAddressComponents(place, input);
      }
    });
  }
}
