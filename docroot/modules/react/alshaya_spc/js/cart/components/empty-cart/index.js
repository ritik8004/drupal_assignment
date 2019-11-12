import React from 'react';

export default class EmptyCart extends React.Component {

  render() {
    return <span>{Drupal.t('Your shopping basket is empty')}</span>
  }

}
