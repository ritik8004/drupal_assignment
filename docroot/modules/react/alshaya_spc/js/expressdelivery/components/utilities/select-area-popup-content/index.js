import React from 'react';

const SelectAreaPopupContent = ({ children, className = '' }) => {
  const closeModal = (e) => {
    if (e.target.classList.contains('select-area-popup-content')) {
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
      className={`select-area-popup-content ${className}`}
      onClick={closeModal}
    >
      {children}
    </div>
  );
};

export default SelectAreaPopupContent;
