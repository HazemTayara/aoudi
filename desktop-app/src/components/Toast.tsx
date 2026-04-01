import { useEffect } from 'react';

interface ToastProps {
  message: string;
  type: 'success' | 'error' | 'warning' | 'info';
  show: boolean;
  onClose: () => void;
}

function Toast({ message, type, show, onClose }: ToastProps) {
  useEffect(() => {
    if (show) {
      const timer = setTimeout(onClose, 3000);
      return () => clearTimeout(timer);
    }
  }, [show, onClose]);

  if (!show) return null;

  const bgClass = {
    success: 'bg-success',
    error: 'bg-danger',
    warning: 'bg-warning',
    info: 'bg-info',
  }[type];

  return (
    <div className="toast-container">
      <div className={`toast show ${bgClass} text-white`}>
        <div className="toast-body d-flex justify-content-between align-items-center">
          <span>{message}</span>
          <button type="button" className="btn-close btn-close-white" onClick={onClose}></button>
        </div>
      </div>
    </div>
  );
}

export default Toast;
