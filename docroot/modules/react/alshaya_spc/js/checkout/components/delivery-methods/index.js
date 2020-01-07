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
      <div>
        <SectionTitle>{Drupal.t('Delivery method')}</SectionTitle>
        <div>
          <input id="delivery-method" checked={this.state.selectedOption === 'hd'} value="hd" name="delivery-method" type="radio" onChange={this.handleChange} />
          {Drupal.t('Home delivery')} <span>{hd_subtitle}</span>
        </div>
        <div>
          <input id="delivery-method" checked={this.state.selectedOption === 'cnc'} disabled={this.props.cnc_disabled} value="cnc" name="delivery-method" type="radio" onChange={this.handleChange} />
          {Drupal.t('Click & Collect')} <span>{cnc_subtitle}</span>
        </div>
      </div>
    )
  }

}
