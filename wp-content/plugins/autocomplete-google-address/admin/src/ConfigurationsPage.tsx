import { useState } from 'react';
import ConfigForm from './components/ConfigForm';
import ConfigList from './components/ConfigList';

const ConfigurationsPage = () => {
  const [isPopupOpen, setIsPopupOpen] = useState(false);
  const [selectedType, setSelectedType] = useState<string | null>(null);
  const [selectedConfig, setSelectedConfig] = useState<any | null>(null);
  const [reload, setReload] = useState(false);

  const handleOpenPopup = (type: string) => {
    setSelectedType(type);
    setSelectedConfig(null); // Reset config when creating a new configuration
    setIsPopupOpen(true);
  };

  const handleEdit = (config: any) => {
    setSelectedType(config.type);
    setSelectedConfig(config); // Load the selected config for editing
    setIsPopupOpen(true);
  };

  const handleClosePopup = () => {
    setIsPopupOpen(false);
    setSelectedType(null);
    setSelectedConfig(null);
  };

  const handleSave = () => {
    console.log('Configuration saved successfully.');
    handleClosePopup();
    setReload(!reload); // Trigger ConfigList to reload data
  };

  return (
    <div className="p-4">
      <h1 className="text-2xl font-semibold mb-4">
        Google Autocomplete Configurations
      </h1>
      <p className="description">
        Manage your Google Autocomplete configurations.
      </p>

      <div className="flex space-x-4 mb-6">
        <button
          onClick={() => handleOpenPopup('regular')}
          className="bg-blue-500 text-white py-2 px-4 rounded"
        >
          New Autocomplete
        </button>
      </div>

      {/* Config List */}
      <ConfigList reload={reload} onEdit={handleEdit} />

      {/* Popup Modal */}
      {isPopupOpen && (
        <div className="fixed inset-0 bg-gray-800 bg-opacity-75 flex justify-center items-center z-50">
          <div className="bg-white p-6 rounded shadow-lg max-w-4xl w-full h-[90vh] relative overflow-hidden">
            <button
              className="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
              onClick={handleClosePopup}
            >
              ✖
            </button>
            {/* Scrollable Form */}
            <div className="h-full overflow-y-auto p-4">
              <ConfigForm
                type={selectedType || ''}
                config={selectedConfig}
                onSave={handleSave}
              />
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ConfigurationsPage;
