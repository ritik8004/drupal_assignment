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
    enabledFieldsWithMessages,
    isEmbeddedForm,
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
        // This prop is an object where object keys are field-names which will
        // be enabled in the form and values are default message on the field
        // example {mobile: Please update mobile number}
        enabledFieldsWithMessages={enabledFieldsWithMessages}
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
      // This prop is an object where object keys are field-names which will
      // be enabled in the form and values are default message on the field
      // example {mobile: Please update mobile number}
      enabledFieldsWithMessages={enabledFieldsWithMessages}
      isEmbeddedForm={isEmbeddedForm}
    />
  );
};
export default AddressContent;
