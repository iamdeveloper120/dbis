// React/src/ClientProgramTreeView/index.jsx
import ReactDOM from 'react-dom/client';
import App from './App';

const rootElement = document.getElementById('client-program-app');
if (rootElement) {
  ReactDOM.createRoot(rootElement).render(<App />);
}
