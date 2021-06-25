import React from 'react';

const PdpPopupContent = ({ children, className = '' }) => {
  const closeModal = (e) => {
    const selectOverlayClass = ['overlay-select', 'overlay-related-select'];
    if (e.target.classList.contains('magv2-pdp-popup-content')) {
      selectOverlayClass.forEach((el) => {
        if (document.querySelector('body').classList.contains(el)) {
          document.querySelector('body').classList.remove(el);
        }
      });

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
