import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import App from './App.tsx';
import './index.css';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import WhatsAppChatButton from './components/WhatsAppChatButton.tsx';
createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <ToastContainer />
    <App />
    <WhatsAppChatButton />
  </StrictMode>,
);
