import React from 'react';

import SectionTitle from '../../../utilities/section-title';

export default class DeliveryMethods extends React.Component {

  constructor(props) {
    super(props);
    let delivery_type = this.props.delivery_type;
    if (this.props.delivery_type === undefined) {
      delivery_type = 'hd';
    }

    this.state = {
      'selectedOption': delivery_type
    };
  }

  handleChange = (e) => {
    const { name, value } = e.target;
    this.setState({
      selectedOption: value
    });
  };

  render() {
    let hd_subtitle = Drupal.t('Standard delivery for purchases over KD 250');
    let cnc_subtitle = window.drupalSettings.cnc_subtitle_available || '';

    // If CNC is disabled.
    if (this.props.cnc_disabled) {
      cnc_subtitle = window.drupalSettings.cnc_subtitle_unavailable || '';
    }

    return (
      <div className="spc-checkout-delivery-methods">
        <SectionTitle>{Drupal.t('delivery method')}</SectionTitle>
        <div className="delivery-method">
          <input id="delivery-method" checked={this.state.selectedOption === 'hd'} value="hd" name="delivery-method" type="radio" onChange={this.handleChange} />
          <label className="radio-sim radio-label">
            <span className="icon"></span>
            <span className="impress">{Drupal.t('home delivery')}</span>
            {hd_subtitle}
          </label>
        </div>
        <div className="delivery-method">
          <input id="delivery-method" checked={this.state.selectedOption === 'cnc'} disabled={this.props.cnc_disabled} value="cnc" name="delivery-method" type="radio" onChange={this.handleChange} />
          <label className="radio-sim radio-label">
            <span className="icon"></span>
            <span className="impress">{Drupal.t('click & collect')}</span>
            {cnc_subtitle}
          </label>
        </div>
      </div>
    )
  }
}
