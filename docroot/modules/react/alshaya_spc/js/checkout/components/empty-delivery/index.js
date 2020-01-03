import React from 'react';

export default class EmptyDeliveryText extends React.Component {

  render() {
    return (
      <div>{Drupal.t('Add your address and contact details')}</div>
    );
  }

}
