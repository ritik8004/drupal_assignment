import React from 'react';
import ReactDOM from 'react-dom';
import MyAccount from './components/my-account';
import isEgiftCardEnabled from '../../js/utilities/egiftCardHelper';

if (isEgiftCardEnabled()) {
  ReactDOM.render(
    <MyAccount />,
    document.querySelector('#my-egift-card'),
  );
}
