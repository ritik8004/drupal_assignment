import React from 'react';

export default class CheckoutItemImage extends React.Component {

  render() {
    if (this.props.img_data !== undefined) {
      return <img src={this.props.img_data.url} alt={this.props.img_data.alt} title={this.props.img_data.title} />
    }

    return (null);
  }

}
