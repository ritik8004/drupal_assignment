import React from 'react';

class EmptyResult extends React.Component {
  render () {
    return <div className="spc-empty-container"><div className="spc-empty-text">{this.props.Message}</div></div>
  }
}

export default EmptyResult;
