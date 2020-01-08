import React from 'react';
import Price from "../price";
import ToolTip from "../tooltip";

class TotalLineItem extends React.Component {
  render() {
    if (typeof this.props.value === 'string' || this.props.value instanceof String) {
      return (
        <div className="total-line-item">
          <span className={this.props.name}>{this.props.title}
            <ToolTip content={this.props.tooltipContent} enable={this.props.tooltip}/>
          </span>
          <span className="value"><span>{this.props.value}</span></span>
        </div>
      );
    }
    else {
      if (this.props.value == 0) {
        return (null);
      }
      return (
        <div className="total-line-item">
          <span className={this.props.name}>{this.props.title}
            <ToolTip content={this.props.tooltipContent} enable={this.props.tooltip}/>
          </span>
          <span className="value"><Price price={this.props.value}/></span>
        </div>
      );
    }
  }
}

export default TotalLineItem;
