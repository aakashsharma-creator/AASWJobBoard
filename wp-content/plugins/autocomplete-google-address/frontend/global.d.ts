// global.d.ts
export interface AddressConfiguration {
  id: number | null | undefined;
  name: string | null | undefined;
  street_address_id: string;
  city_id?: string | null | undefined;
  state_id?: string | null | undefined;
  zip_id?: string | null | undefined;
  country_id?: string | null | undefined;
  sub_locality?: string | null | undefined;
  county?: string | null | undefined;
  latitude?: string | null | undefined;
  longitude?: string | null | undefined;
  country_restriction?: string[] | [];
  country_type?: "short" | "long" | null | undefined;
  state_type?: "short" | "long" | null | undefined;
  address_type?: "short" | "long" | null | undefined;
  show_map?: boolean;
  optional_1?: string | null | undefined;
  optional_2?: string | null | undefined;
  search_type?: string[];
  type?: "regular" | "woocommerce" | null | undefined;
  map_width: string;
  map_height: string;
  map_display_id: string;
  zoom_level: number;
  // address_format: "address" | "name";
}

declare global {
  interface Window {
    jQuery: (selector: string) => JQueryStatic;
    wc_checkout_form: {
      update_checkout: () => void;
    };
    gapFrontendData: GapFrontendData;
    initGoogleMaps: () => void;
  }
}

declare namespace google.maps.marker {
  class AdvancedMarkerElement extends HTMLElement {
    position: google.maps.LatLng | google.maps.LatLngLiteral;
    map: google.maps.Map | null;
    draggable: boolean;

    constructor(options?: AdvancedMarkerElementOptions);

    addListener(
      eventName: string,
      handler: (event: google.maps.MapMouseEvent) => void
    ): google.maps.MapsEventListener;
  }

  interface AdvancedMarkerElementOptions {
    position?: google.maps.LatLng | google.maps.LatLngLiteral;
    map?: google.maps.Map;
    draggable?: boolean;
  }
}
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

export {}; // Ensures the file is treated as a module
