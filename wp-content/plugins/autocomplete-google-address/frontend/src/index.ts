import { initializeGoogleMaps } from "./googleMaps";
import { initGoogleMaps } from "./initMap";

// Declare wc_checkout_params if TypeScript still doesn't recognize it
declare const wc_checkout_params: {
  ajax_url: string;
  wc_ajax_url: string;
  update_order_review_nonce: string;
  apply_coupon_nonce: string;
  remove_coupon_nonce: string;
  option_guest_checkout: string;
  checkout_url: string;
  is_checkout: string;
  debug_mode: string;
  i18n_checkout_error: string;
};

function waitForElement(
  elementId: string,
  callback: (element: HTMLElement) => void
): void {
  const interval = setInterval(() => {
    const element = document.getElementById(elementId);
    if (element) {
      clearInterval(interval);
      callback(element);
    }
  }, 100); // Check every 100ms
}

window.initGoogleMaps = function () {
  if (!window.gapFrontendData || !window.gapFrontendData.configs) {
    console.error("gapFrontendData or configs is missing.");
    return;
  }

  const { configs, googleApiKey } = window.gapFrontendData;
  console.log("Configs received:", configs);

  configs.forEach((config: any) => {
    waitForElement(config.street_address_id, (element) => {
      // console.log(`Element found for ID: ${config.street_address_id}`);
      initializeGoogleMaps(googleApiKey, () => {
        initGoogleMaps();
      });
    });
  });
};

document.addEventListener("DOMContentLoaded", () => {
  if (
    typeof wc_checkout_params !== "undefined" &&
    wc_checkout_params.is_checkout === "1"
  ) {
    console.log(
      "WooCommerce Checkout Page Detected. Initializing Google Maps..."
    );

    window.initGoogleMaps();

    jQuery(document.body).on("updated_checkout", () => {
      console.log(
        "Checkout page dynamically updated. Reinitializing Google Maps..."
      );
      window.initGoogleMaps();
    });
  } else {
    console.log(
      "This is not a WooCommerce checkout page. Skipping Google Maps initialization."
    );
  }
});
