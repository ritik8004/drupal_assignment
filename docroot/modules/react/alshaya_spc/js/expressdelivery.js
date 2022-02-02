import React from 'react';
import ReactDOM from 'react-dom';
import DeliveryOptions from './expressdelivery/components/delivery-options';
import WaitForElement from '../../js/utilities/wait_for_element';

WaitForElement('#express-delivery-options')
  .then(() => {
    ReactDOM.render(
      <DeliveryOptions />,
      document.getElementById('express-delivery-options'),
    );
  });
