import { useEffect, useState } from 'react';
import { toast } from 'react-toastify';

const ConfigList = ({
  reload,
  onEdit,
}: {
  reload: boolean;
  onEdit: (config: any) => void;
}) => {
  const [configs, setConfigs] = useState([]);

  useEffect(() => {
    const fetchConfigs = async () => {
      try {
        console.log('Fetching configurations...');
        const response = await fetch(`${window.gapData.restUrl}configs`, {
          method: 'GET',
          headers: {
            'X-WP-Nonce': window.gapData.nonce, // Include the nonce
          },
          credentials: 'same-origin', // Ensure cookies are sent
        });

        if (!response.ok) {
          console.error('HTTP Error:', response.status);
          console.log('Headers:', response.headers);
          throw new Error('Failed to fetch configurations.');
        }

        const data = await response.json();
        console.log('Fetched Configurations:', data);
        setConfigs(data);
      } catch (error) {
        console.error('Error fetching configurations:', error);
      }
    };

    fetchConfigs();
  }, [reload]);

  const handleDelete = async (id: number) => {
    try {
      const response = await fetch(window.gapData.restUrl + `configs/${id}`, {
        method: 'DELETE',
        headers: {
          'X-WP-Nonce': window.gapData.nonce,
        },
      });

      if (response.ok) {
        setConfigs((prev) => prev.filter((config: any) => config.id !== id));
        toast.success('Configuration deleted successfully.');
        console.log('Configuration deleted successfully.');
      } else {
        toast.error('Failed to delete configuration.');
        // console.error('Failed to delete configuration.');
      }
    } catch (error) {
      console.error('Error deleting configuration:', error);
    }
  };

  return (
    <div className="grid grid-cols-2 gap-4 mt-5">
      {configs.map((config: any) => (
        <div key={config.id} className="bg-white p-4 shadow rounded">
          <h3 className="text-lg font-semibold">{config.name}</h3>
          <div className="overflow-x-auto">
            <table className="min-w-full border-collapse border border-gray-300 text-sm">
              <thead>
                <tr>
                  <th className="border border-gray-300 px-2 py-1 text-left">
                    Key
                  </th>
                  <th className="border border-gray-300 px-2 py-1 text-left">
                    Value
                  </th>
                </tr>
              </thead>
              <tbody>
                {Object.entries(config)
                  .filter(
                    ([_, value]) =>
                      value !== null && value !== 'N/A' && value !== '',
                  ) // Filter out keys with "N/A", null, or empty values
                  .map(([key, value]) => (
                    <tr key={key}>
                      <td className="border border-gray-300 px-2 py-1">
                        {key.replace(/_/g, ' ').toUpperCase()}
                      </td>
                      <td className="border border-gray-300 px-2 py-1">
                        {String(value)}
                      </td>
                    </tr>
                  ))}
              </tbody>
            </table>
          </div>

          <div className="mt-4">
            <button
              onClick={() => onEdit(config)}
              className="button button-secondary"
              style={{ marginRight: '10px' }}
            >
              Edit
            </button>
            <button
              onClick={() => handleDelete(config.id)}
              className="button button-link-delete"
            >
              Delete
            </button>
          </div>
        </div>
      ))}
    </div>
  );
};

export default ConfigList;
