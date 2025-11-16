type Option = {
  value: string;
  label: string;
};
export const options: Option[] = [
  { value: 'all', label: 'All' },
  { value: 'geocode', label: 'Geocode' },
  { value: 'address', label: 'Address' },
  { value: 'cities', label: 'Cities' },
  { value: 'establishment', label: 'Establishment' },
  { value: '(regions)', label: 'Regions' },
  { value: 'postal_code', label: 'Postal Code' },
  { value: 'airport', label: 'Airport' },
  { value: 'locksmith', label: 'Locksmith' },
  { value: 'plumber', label: 'Plumber' },
  { value: 'post_office', label: 'Post Office' },
  { value: 'restaurant', label: 'Restaurant' },
  { value: 'roofing_contractor', label: 'Roofing Contractor' },
];

export type Config = {
  id?: string | null;
  name?: string;
  street_address_id?: string;
  city_id?: string;
  state_id?: string;
  zip_id?: string;
  country_id?: string;
  sub_locality?: string;
  county?: string;
  latitude?: string;
  longitude?: string;
  country_restriction?: string[]; // Array of country codes (e.g., ["BD", "US"])
  country_type?: string;
  state_type?: string;
  address_type?: string;
  show_map?: boolean;
  optional_1?: string;
  optional_2?: string;
  search_type?: string[];
  map_width?: number;
  map_height?: string;
  map_display_id?: string;
  zoom_level?: number;
};
