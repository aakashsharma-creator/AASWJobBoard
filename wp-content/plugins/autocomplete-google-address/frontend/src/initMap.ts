import { initAutocomplete } from "./initAutocomplete";
import { AddressConfiguration, ComponentRestrictions } from "./types/index";

export function initGoogleMaps() {
  const { configs: formSetup } = window.gapFrontendData;

  formSetup.forEach((input: AddressConfiguration, index: number) => {
    const countryList: ComponentRestrictions = {
      country: input.country_restriction,
    };

    const mapContainerId = `map-${input.street_address_id}-${index}`;

    const inputElement = document.getElementById(input.street_address_id);
    if (!inputElement) {
      console
        .error
        // `Input element not found for ID: ${input.street_address_id}`
        ();
      return;
    }

    let mapContainer: HTMLElement | null = null;

    // If map_display_id is a class, find the first element with that class
    if (input.map_display_id && input.map_display_id !== "parent") {
      const mapDisplayElements = document.getElementsByClassName(
        input.map_display_id
      );
      if (mapDisplayElements.length > 0) {
        // Use the first element with the specified class
        const mapDisplayElement = mapDisplayElements[0] as HTMLElement;

        // Check if map container already exists
        mapContainer = mapDisplayElement.querySelector(
          `#${mapContainerId}`
        ) as HTMLElement;
        if (!mapContainer) {
          // Create a new map container
          mapContainer = document.createElement("div");
          mapContainer.id = mapContainerId;
          mapContainer.style.width = `${input.map_width}%`;
          mapContainer.style.height = `${parseInt(input.map_height, 10)}px`;
          mapContainer.style.margin = "10px"; // Optional styling

          // Append the map container to the specified element
          mapDisplayElement.appendChild(mapContainer);
        }
      } else {
        console.error(
          `No element found with class name ${input.map_display_id}. Cannot display map.`
        );
        return;
      }
    } else {
      // Default to appending the map container near the input field
      if (inputElement.parentElement) {
        mapContainer = inputElement.parentElement.querySelector(
          `#${mapContainerId}`
        ) as HTMLElement;
        if (!mapContainer) {
          mapContainer = document.createElement("div");
          mapContainer.id = mapContainerId;
          mapContainer.style.width = `${input.map_width}%`;
          mapContainer.style.height = `${parseInt(input.map_height, 10)}px`;
          inputElement.parentElement.appendChild(mapContainer);
        }
      } else {
        console.error(
          `Parent element for input ID ${input.street_address_id} not found.`
        );
        return;
      }
    }

    // Initialize Autocomplete with the updated mapContainer
    if (mapContainer) {
      initAutocomplete(input, countryList, mapContainer.id);
    }
  });
}
