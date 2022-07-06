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
    shippingAsBilling = null,
    areaUpdated,
    isExpressDeliveryAvailable,
    fillDefaultValue,
  } = props;

  // For users who are logged in and have saved an address.
  if (drupalSettings.user.uid > 0
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
        areaUpdated={areaUpdated}
        isExpressDeliveryAvailable={isExpressDeliveryAvailable}
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
      shippingAsBilling={shippingAsBilling}
      isExpressDeliveryAvailable={isExpressDeliveryAvailable}
      fillDefaultValue={fillDefaultValue}
    />
  );
};
export default AddressContent;
