import ConfigurationsPage from './ConfigurationsPage';
import SettingsPage from './components/SettingsPage';

const pageContext = window.gapPageContext;
console.log('Current Page Context:', pageContext);
console.log(window.gapData);
const App = () => {
  if (pageContext === 'settings') {
    return <SettingsPage />;
  }
  return <ConfigurationsPage />;
};

export default App;
