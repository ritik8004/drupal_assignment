import React from 'react';

class ToolTip extends React.Component {
  getHtmlMarkup() {
    return { __html: this.props.content };
  }

  render() {
    const iconClass = this.props.question === true ? ' question' : ' info';
    if (this.props.enable) {
      return (
        <div className={`tooltip-anchor${iconClass}`}>
          <div className="tooltip-box" dangerouslySetInnerHTML={this.getHtmlMarkup()} />
        </div>
      );
    }
    return (null);
  }
}

export default ToolTip;
