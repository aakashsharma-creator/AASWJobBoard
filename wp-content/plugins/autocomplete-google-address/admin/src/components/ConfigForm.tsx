import React, { useState } from 'react';
import Select from 'react-select';
import { Config, options } from '../lib';
import { toast } from 'react-toastify';
import { countries, Country } from '../lib/CountryList';

// import { Country } from '../lib/CountryList';

interface ConfigFormProps {
  type: string;
  config?: Config;
  onSave: () => void;
}

const ConfigForm: React.FC<ConfigFormProps> = ({ type, config, onSave }) => {
  const isPlan = window.gapData.isPlan;
  const [formData, setFormData] = useState({
    id: config?.id || null,
    name: config?.name || '',
    street_address_id: config?.street_address_id,
    city_id: config?.city_id,
    state_id: config?.state_id,
    zip_id: config?.zip_id,
    country_id: config?.country_id,
    sub_locality: config?.sub_locality || '',
    county: config?.county || '',
    latitude: config?.latitude || '34.052235',
    longitude: config?.longitude || '-118.243683',
    country_restriction: config?.country_restriction || [], // Initial country_restriction
    country_type: config?.country_type || 'short',
    state_type: config?.state_type || 'short',
    address_type: config?.address_type || 'long',
    show_map: config?.show_map || false,
    optional_1: config?.optional_1 || '',
    optional_2: config?.optional_2 || '',
    search_type: config?.search_type || ['address'],
    map_width: config?.map_width || 100,
    map_height: config?.map_height || '250',
    map_display_id: config?.map_display_id || 'parent',
    zoom_level: config?.zoom_level || 18,
  });

  const handleCountryChange = (selectedOptions: any) => {
    const selectedCountries = selectedOptions
      ? selectedOptions.map((option: any) => option.value)
      : [];
    setFormData((prevData) => ({
      ...prevData,
      country_restriction: selectedCountries.slice(0, 5), // Ensure no more than 5 countries
    }));
  };

  const handleSearchTypeChange = (selectedOption: any) => {
    setFormData((prevData) => ({
      ...prevData,
      search_type: selectedOption ? [selectedOption.value] : ['address'], // If no selection, default to 'address'
    }));
  };
  console.log('check multi select=======>', formData);

  const handleInputChange = (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>,
  ) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleToggleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, checked } = e.target;
    setFormData((prev) => ({ ...prev, [name]: checked }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!formData.name) {
      toast.error('Configuration name is required.'); // Error toast
      return;
    }

    console.log('Submitting form data:', formData);

    try {
      const url = `${window.gapData.restUrl}configs${formData.id ? `/${formData.id}` : ''}`;
      const method = formData.id ? 'PUT' : 'POST';

      const response = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': window.gapData.nonce,
        },
        body: JSON.stringify({ ...formData, type }),
      });

      if (response.ok) {
        const responseData = await response.json();
        console.log('Configuration saved successfully:', responseData);
        toast.success('Configuration saved successfully!'); // Success toast
        onSave(); // Refresh parent component or reload configurations
      } else {
        const errorData = await response.json();
        console.error('Failed to save configuration:', errorData);
        toast.error('Failed to save configuration. Please try again.'); // Error toast
      }
    } catch (error) {
      console.error('Error saving configuration:', error);
      toast.error('An unexpected error occurred. Please try again.'); // Error toast
    }
  };
  console.log('Has Pro Plan=================>', window.gapData);

  return (
    <form onSubmit={handleSubmit} className="bg-white p-4 shadow rounded">
      <h2 className="text-lg font-semibold mb-4">
        {formData.id ? 'Edit Configuration' : 'New Configuration'} - {type}
      </h2>

      <div className="grid grid-cols-2 gap-4">
        <label>
          Configuration Name:
          <input
            type="text"
            name="name"
            placeholder="Configuration Name"
            value={formData.name}
            onChange={handleInputChange}
            className="border p-1 w-full"
          />
        </label>
        <label>
          Street Address Input ID:
          <input
            type="text"
            name="street_address_id"
            placeholder="Street Address Input ID"
            value={formData.street_address_id}
            onChange={handleInputChange}
            className="border p-1 w-full"
          />
        </label>
        <label>
          City Input ID:
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <input
            disabled={!isPlan}
            type="text"
            name="city_id"
            placeholder="City Input ID"
            value={formData.city_id}
            onChange={handleInputChange}
            className="border p-1 w-full"
          />
        </label>
        <label>
          State Input ID:
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <input
            disabled={!isPlan}
            type="text"
            name="state_id"
            placeholder="State Input ID"
            value={formData.state_id}
            onChange={handleInputChange}
            className="border p-1 w-full"
          />
        </label>
        <label>
          Zip Code Input ID:
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <input
            disabled={!isPlan}
            type="text"
            name="zip_id"
            placeholder="Zip Code Input ID"
            value={formData.zip_id}
            onChange={handleInputChange}
            className="border p-1 w-full"
          />
        </label>
        <label>
          Country Input ID:
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <input
            disabled={!isPlan}
            type="text"
            name="country_id"
            placeholder="Country Input ID"
            value={formData.country_id}
            onChange={handleInputChange}
            className="border p-1 w-full"
          />
        </label>
        <label>
          Sub-locality:
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <input
            disabled={!isPlan}
            type="text"
            name="sub_locality"
            placeholder="Sub-locality"
            value={formData.sub_locality}
            onChange={handleInputChange}
            className="border p-1 w-full"
          />
        </label>
        <label>
          County:
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <input
            disabled={!isPlan}
            type="text"
            name="county"
            placeholder="County"
            value={formData.county}
            onChange={handleInputChange}
            className="border p-1 w-full"
          />
        </label>
        <label>
          Latitude:
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <input
            disabled={!isPlan}
            type="text"
            name="latitude"
            placeholder="Latitude"
            value={formData.latitude}
            onChange={handleInputChange}
            className="border p-1 w-full"
          />
        </label>
        <label>
          Longitude:
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <input
            disabled={!isPlan}
            type="text"
            name="longitude"
            placeholder="Longitude"
            value={formData.longitude}
            onChange={handleInputChange}
            className="border p-1 w-full"
          />
        </label>
        <label>
          Country Restriction:
          {/* {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )} */}
          <Select
            isMulti
            options={countries}
            getOptionLabel={(e: Country) => e.name}
            getOptionValue={(e: Country) => e.value}
            value={countries.filter((country) =>
              formData.country_restriction.includes(country.value),
            )}
            onChange={handleCountryChange}
            maxMenuHeight={200}
            placeholder="Select up to 5 countries"
            isDisabled={
              formData.country_restriction.length >= 5 &&
              !formData.country_restriction.length
            } // Disable adding more if 5 countries selected
          />
        </label>
        <label>
          Country Type:
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <select
            disabled={!isPlan}
            name="country_type"
            value={formData.country_type}
            onChange={handleInputChange}
            className="border p-1 w-full"
          >
            <option value="short">Short</option>
            <option value="long">Long</option>
          </select>
        </label>
        <label>
          State Type
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <select
            disabled={!isPlan}
            name="state_type"
            value={formData.state_type}
            onChange={handleInputChange}
            className="border p-1 w-full"
          >
            <option value="short">Short</option>
            <option value="long">Long</option>
          </select>
        </label>
        <label>
          Search Type:
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <Select
            isDisabled={!isPlan}
            options={options} // Your select options
            value={options.find(
              (option) => option.value === formData.search_type[0],
            )} // Set the selected value based on formData
            onChange={handleSearchTypeChange} // Handle the selection change
            className="border p-1 w-full"
          />
        </label>

        <label>
          Address Type:
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <select
            disabled={!isPlan}
            name="address_type"
            value={formData.address_type}
            onChange={handleInputChange}
            className="border p-1 w-full"
          >
            <option value="short">Short</option>
            <option value="long">Long</option>
          </select>
        </label>
        {/* map control */}
        <label>
          Show Map:
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <input
            disabled={!isPlan}
            type="checkbox"
            name="show_map"
            checked={formData.show_map}
            onChange={handleToggleChange}
          />
        </label>
        <label>
          Map Width: <span>{formData.map_width}%</span>
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <input
            disabled={!isPlan}
            type="range"
            min={1}
            max={100}
            value={formData.map_width}
            className="slider"
            id="auto-slider"
            name="map_width"
            onChange={handleInputChange}
          />
        </label>
        <label>
          Map Height:
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <input
            disabled={!isPlan}
            type="number"
            name="map_height"
            placeholder="Map Height (e.g., 400px)"
            value={formData.map_height}
            onChange={handleInputChange}
            className="border p-1 w-full"
          />
        </label>

        <label>
          Map Display ClassName:
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <input
            disabled={!isPlan}
            type="text"
            name="map_display_id"
            placeholder="e.g: checkout_left"
            value={formData.map_display_id}
            onChange={handleInputChange}
            className="border p-1 w-full"
          />
        </label>
        <label>
          Zoom Level:
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <input
            disabled={!isPlan}
            type="number"
            name="zoom_level"
            placeholder="Enter zoom level (e.g., 13)"
            value={formData.zoom_level}
            onChange={handleInputChange}
            className="border p-1 w-full"
          />
        </label>

        {/* map control */}
        <label>
          Optional 1:
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <input
            disabled={!isPlan}
            type="text"
            name="optional_1"
            placeholder="Optional 1"
            value={formData.optional_1}
            onChange={handleInputChange}
            className="border p-1 w-full"
          />
        </label>
        <label>
          Optional 2:
          {!isPlan && (
            <span className="ml-2">
              <b className="text-red-500">* Premium Only</b>
            </span>
          )}
          <input
            disabled={!isPlan}
            type="text"
            name="optional_2"
            placeholder="Optional 2"
            value={formData.optional_2}
            onChange={handleInputChange}
            className="border p-1 w-full"
          />
        </label>
      </div>
      <div className="mt-4 flex space-x-4">
        <button type="submit" className="button button-primary">
          Save
        </button>
        <button
          type="button"
          onClick={onSave}
          className="ml-2 button button-secondary"
        >
          Cancel
        </button>
      </div>
    </form>
  );
};

export default ConfigForm;
