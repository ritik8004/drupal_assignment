import React from 'react';

export default class CompletePurchase extends React.Component {

  render() {
    let class_name = this.props.selected_payment_method !== null
      ? 'active'
      : 'in-active';
    return (
      <div className={"checkout-link submit " + class_name}>
        <a href={Drupal.url('checkout')} className="checkout-link">
          {Drupal.t('complete purchase')}
        </a>
      </div>
    );
  }

}
