import React from 'react';
import AddressList from '../address-list';
import AddressForm from '../address-form';

const AddressContent = (props) => {
  const {
    cart,
    closeModal,
    default_val,
    showEmail,
    processAddress,
    headingText,
    showEditButton,
    type
  } = props;

  if (window.drupalSettings.user.uid > 0
    && drupalSettings.user_name.address_available !== undefined
    && drupalSettings.user_name.address_available === true) {
    return (
      <AddressList
        cart={cart}
        closeModal={closeModal}
        headingText={headingText}
        showEditButton={showEditButton}
        processAddress={processAddress}
        type={type}
      />
    );
  }

  return (
    <AddressForm
      closeModal={closeModal}
      default_val={default_val}
      showEmail={showEmail}
      headingText={headingText}
      processAddress={processAddress}
    />
  );
};
export default AddressContent;
