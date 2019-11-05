import React from 'react';
import Price from "../../../utilities/price";

class TotalLineItem extends React.Component {
  render() {
    if (typeof this.props.value === 'string' || this.props.value instanceof String) {
      return (
        <div className="total-line-item">
          <span className={this.props.name}>{this.props.title}</span>
          <span className="value"><span>{this.props.value}</span></span>
        </div>
      );
    }
    else {
      return (
        <div className="total-line-item">
          <span className={this.props.name}>{this.props.title}</span>
          <span className="value"><Price price={this.props.value}/></span>
        </div>
      );
    }
  }
}

export default TotalLineItem;
