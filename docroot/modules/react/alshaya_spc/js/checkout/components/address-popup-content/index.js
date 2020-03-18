import React from 'react';
import AddressList from '../address-list';
import AddressForm from '../address-form';

const AddressContent = (props) => {
  const {
    cart,
    closeModal,
    show_prefered,
    default_val,
    showEmail,
    processAddress,
  } = props;

  if (window.drupalSettings.user.uid > 0
    && cart.cart.shipping_address !== null) {
    return <AddressList cart={cart} closeModal={closeModal} />;
  }

  return (
    <AddressForm
      show_prefered={show_prefered}
      closeModal={closeModal}
      default_val={default_val}
      showEmail={showEmail}
      processAddress={processAddress}
    />
  );
};
export default AddressContent;
