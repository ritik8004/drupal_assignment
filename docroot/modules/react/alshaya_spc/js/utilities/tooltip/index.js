import React from 'react';

class ToolTip extends React.Component {
  render() {
    if (this.props.enable) {
      return (
        <div className="tooltip-anchor">
          <div className="tooltip-box">{this.props.content}</div>
        </div>
      )
    }
    return (null);
  }
}

export default ToolTip;
