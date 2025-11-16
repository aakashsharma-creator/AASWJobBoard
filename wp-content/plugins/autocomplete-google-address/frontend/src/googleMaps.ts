export function initializeGoogleMaps(
  apiKey: string,
  callback: () => void
): void {
  // Check if the Google Maps API is already loaded
  if (typeof google !== "undefined" && google.maps) {
    callback();
    return;
  }
}
