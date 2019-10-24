import React from 'react';

export default class RecommendedProduct extends React.Component {

  render() {
    const item = this.props.item;
    return (
      <div>
        <img src={item.medias.images[0].url} />
        <div>{item.title}</div>
      </div>
    );
  }
}
