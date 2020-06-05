import React from 'react';

export default class PdpClickCollectSearch extends React.PureComponent {
  changeLabel = (event) => {
    const { onChange } = this.props;

    onChange(event.target.value);
  };

  render() {
    const { changeLabel } = this;
    const { inputValue } = this.props;

    return (
      <div className="location-field-wrapper">
        <div className="location-field">
          <input placeholder="e.g. Salmiya" onChange={changeLabel} value={inputValue} />
        </div>
        <div className="location-field-icon" />
      </div>
    );
  }
}
