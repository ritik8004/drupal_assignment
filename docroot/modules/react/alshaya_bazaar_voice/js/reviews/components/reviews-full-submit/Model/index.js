import React from 'react';
import ReactDOM from 'react-dom';
import FocusTrap from 'focus-trap-react';
import Form from '../Form';

const Modal = ({
  onClickOutside,
  onKeyDown,
  modalRef,
  buttonRef,
  closeModal,
  formData,
}) => ReactDOM.createPortal(
  <FocusTrap>
    <aside
      tag="aside"
      role="dialog"
      tabIndex="-1"
      aria-modal="true"
      className="modal-cover"
      onClick={onClickOutside}
      onKeyDown={onKeyDown}
    >
      <div className="modal-area" ref={modalRef}>
        <a
          ref={buttonRef}
          aria-label="Close Modal"
          aria-labelledby="close-modal"
          className="_modal-close"
          onClick={closeModal}
        >
          <span id="close-modal" className="_hide-visual">
            Close
          </span>
          <svg className="_modal-close-icon" viewBox="0 0 40 40">
            <path d="M 10,10 L 30,30 M 30,10 L 10,30" />
          </svg>
        </a>
        <div className="modal-body">
          <Form formData={formData} />
        </div>
      </div>
    </aside>
  </FocusTrap>,
  document.body,
);

export default Modal;
