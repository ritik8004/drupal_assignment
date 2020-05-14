import React from 'react';
import AddressList from '../address-list';
import AddressForm from '../address-form';

const AddressContent = (props) => {
  const {
    cart,
    closeModal,
    default_val: defaultVal,
    showEmail,
    processAddress,
    headingText,
    showEditButton,
    type,
    formContext,
  } = props;

  if (window.drupalSettings.user.uid > 0
    && cart.cart.customer.addresses !== undefined
    && cart.cart.customer.addresses.length > 0) {
    return (
      <AddressList
        cart={cart}
        closeModal={closeModal}
        headingText={headingText}
        showEditButton={showEditButton}
        processAddress={processAddress}
        type={type}
        formContext={formContext}
      />
    );
  }

  return (
    <AddressForm
      closeModal={closeModal}
      default_val={defaultVal}
      showEmail={showEmail}
      headingText={headingText}
      processAddress={processAddress}
      formContext={formContext}
    />
  );
};
export default AddressContent;
