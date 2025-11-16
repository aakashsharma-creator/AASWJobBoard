import { ComponentRestrictions, AutocompleteOptions } from "../types/index";

export const generateOptions = (
  addressType: string[] = ["address"],
  countryList: ComponentRestrictions
): AutocompleteOptions => {
  return {
    types: addressType[0] === "all" ? [] : addressType, // Set types to [] if addressType is empty,
    fields: [
      "address_components",
      "geometry",
      "icon",
      "name",
      "formatted_address",
      "type",
    ],
    componentRestrictions: countryList,
  };
};
