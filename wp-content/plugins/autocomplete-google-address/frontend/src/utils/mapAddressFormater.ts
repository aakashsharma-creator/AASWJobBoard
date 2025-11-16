import { AddressConfiguration } from "../types/index";
import { getInputByID } from "./getInputByID";
import { longShort } from "./longShort";
import { setInputValue } from "./setInputValue";

export function mapAddressFormater(
  place: google.maps.places.PlaceResult,
  input: AddressConfiguration
) {
  let streetNumber = "";
  let route = "";

  // console.log("test");

  if (place.address_components) {
    place.address_components.forEach(
      (component: google.maps.GeocoderAddressComponent) => {
        const addressType = component.types[0];
        switch (addressType) {
          case "street_number":
            streetNumber = component.long_name;
            break;
          case "route":
            route = component.long_name;
            break;
          case "locality":
            if (input.city_id) {
              setInputValue(input.city_id, component.long_name);
            }
            break;
          case "sub_locality":
            if (input.sub_locality) {
              setInputValue(input.sub_locality, component.long_name);
            }
            break;
          case "postal_town":
            if (input.optional_1) {
              setInputValue(input.optional_1, component.long_name);
            }
            break;
          case "administrative_area_level_1":
            if (input.state_id) {
              setInputValue(
                input.state_id,
                longShort(component, input.state_type)
              );
            }
            break;
          case "administrative_area_level_2":
            if (input.optional_2) {
              setInputValue(input.optional_2, component.long_name);
            }
            break;
          case "postal_code":
            if (input.zip_id) {
              setInputValue(input.zip_id, component.long_name);
            }
            break;
          case "country":
            if (input.country_id) {
              setInputValue(
                input.country_id,
                longShort(component, input.country_type)
              );
            }
            break;
        }
      }
    );
  } else {
    console.error("place.address_components is undefined");
  }

  if (input.address_type === "short") {
    // console.log("test short");

    const shortAddress = `${streetNumber} ${route}`;
    setInputValue(input.street_address_id, shortAddress);
  } else {
    // console.log("test else");

    // console.log("place formated====>", place.formatted_address);

    setInputValue(input.street_address_id, place.formatted_address as string);
  }
}
