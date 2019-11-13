import React from 'react';

export default class CartPromotion extends React.Component {

  render() {
    if (this.props.promo.promo_web_url === undefined) {
      return (null);
    }

    return <span className="promotion-label"><a href={Drupal.url(this.props.promo.promo_web_url)}>{this.props.promo.text}</a></span>;
  }

}
