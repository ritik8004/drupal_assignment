import React from 'react';

const PdpPopupContent = ({ children, className = '' }) => {
  const closeModal = (e) => {
    if (e.target.classList.contains('magv2-pdp-popup-content')) {
      if (document.querySelector('body').classList.contains('overlay-related-select')) {
        document.querySelector('body').classList.remove('overlay-related-select');
      }

      if (children !== null && document.querySelector('body').classList.contains(children.props.overlayClass)) {
        children.props.closeModal();
      }
    }
  };

  return (
    <div
      className={`magv2-pdp-popup-content ${className}`}
      onClick={closeModal}
    >
      {children}
    </div>
  );
};

export default PdpPopupContent;
