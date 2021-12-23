import React from 'react';
import ReactDOM from 'react-dom';
import isEgiftCardEnabled from '../../js/utilities/egiftCardHelper';
import MyEgiftCard from './components/my-account';

if (isEgiftCardEnabled()) {
  ReactDOM.render(
    <MyEgiftCard />,
    document.querySelector('#my-egift-card'),
  );
}
