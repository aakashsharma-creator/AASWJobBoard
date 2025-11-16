export const longShort = (
  component: google.maps.GeocoderAddressComponent,
  type: string
) => {
  if (type == "short") {
    return component.short_name;
  }
  return component.long_name;
};
