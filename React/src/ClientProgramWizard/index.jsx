// React/src/MasterProgramTreeView/index.jsx
 import ReactDOM from 'react-dom/client';
import App from './App';

const rootElement = document.getElementById('client-program-wizard-app');
if (rootElement) {
  ReactDOM.createRoot(rootElement).render(<App />);
}
