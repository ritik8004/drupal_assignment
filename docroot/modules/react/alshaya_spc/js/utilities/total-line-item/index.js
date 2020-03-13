import React from 'react';
import ToolTip from '../tooltip';
import PriceElement from '../special-price/PriceElement';

class TotalLineItem extends React.Component {
  render() {
    if (typeof this.props.value === 'string' || this.props.value instanceof String) {
      return (
        <div className="total-line-item">
          <span className={this.props.name}>
            {this.props.title}
            <ToolTip enable={this.props.tooltip}>{this.props.tooltipContent}</ToolTip>
          </span>
          <span className="value"><span>{this.props.value}</span></span>
        </div>
      );
    }

    if (this.props.value == 0) {
      return (null);
    }
    return (
      <div className="total-line-item">
        <span className={this.props.name}>
          {this.props.title}
          <ToolTip enable={this.props.tooltip}>{this.props.tooltipContent}</ToolTip>
        </span>
        <span className="value"><PriceElement amount={this.props.value} /></span>
      </div>
    );
  }
}

export default TotalLineItem;
