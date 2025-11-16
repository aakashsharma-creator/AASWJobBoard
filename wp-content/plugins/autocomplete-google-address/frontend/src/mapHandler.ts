import { AddressConfiguration } from "./types/index";
import { mapAddressFormater } from "./utils/mapAddressFormater";
const { isPlan } = window.gapFrontendData;

export async function initializeMap(
  mapContainerId: string,
  defaultLat: number,
  defaultLng: number,
  input: AddressConfiguration
): Promise<{
  map: google.maps.Map;
  marker: google.maps.marker.AdvancedMarkerElement;
} | null> {
  const mapContainer = document.getElementById(mapContainerId) as HTMLElement;

  if (!mapContainer) {
    console.error(`Map container with ID ${mapContainerId} not found.`);
    return null;
  }

  const defaultLocation = { lat: defaultLat, lng: defaultLng };

  // Use your Map ID here
  const mapId = "YOUR_MAP_ID"; // Replace with your actual Map ID

  // Initialize the map with individual controls toggled
  const map = new google.maps.Map(mapContainer, {
    center: defaultLocation,
    zoom: Number(input.zoom_level) || 13,
    mapId: mapId,
    zoomControl: true, // Disable Zoom Control (set to true to enable)
    mapTypeControl: false, // Enable Map Type Control (set to false to disable)
    scaleControl: false, // Disable Scale Control
    streetViewControl: false, // Disable Street View Control
    rotateControl: false, // Enable Rotate Control
    fullscreenControl: false, // Enable Fullscreen Control
  });

  // Dynamically import the Marker library
  const markerLibrary = await google.maps.importLibrary("marker");

  // Use AdvancedMarkerElement with proper options
  const { AdvancedMarkerElement } =
    markerLibrary as unknown as typeof google.maps.marker;

  const marker = new AdvancedMarkerElement({
    position: defaultLocation,
    map: map,
    gmpDraggable: true, // Correct property for draggable markers
  });

  // Reverse geocoding service
  const geocoder = new google.maps.Geocoder();

  // Listen for marker drag events
  marker.addListener("dragend", () => {
    const position = marker.position;
    if (position) {
      // Handle lat and lng values safely
      const lat =
        typeof position.lat === "function" ? position.lat() : position.lat;
      const lng =
        typeof position.lng === "function" ? position.lng() : position.lng;

      //   console.log("Marker dragged to:", lat, lng);

      // Reverse geocode the marker's position
      geocoder.geocode({ location: { lat, lng } }, (results, status) => {
        if (status === "OK" && results && results[0]) {
          //   console.log("Reverse geocoded address:", results[0]);

          // Apply the address to the form using processAddressComponents
          if (isPlan) {
            mapAddressFormater(results[0], input);
          }
        } else {
          //   console.error("Geocoder failed or returned no results: " + status);
        }
      });
    }
  });

  return { map, marker };
}
