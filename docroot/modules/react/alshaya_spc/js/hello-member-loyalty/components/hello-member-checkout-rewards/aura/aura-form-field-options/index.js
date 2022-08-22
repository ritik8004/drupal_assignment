import React from 'react';
import getStringMessage from '../../../../../../../js/utilities/strings';
import AuraFormFieldItem from '../aura-form-field-item';

class AuraFormFieldOptions extends React.Component {
  constructor(props) {
    super(props);
    this.optionsRef = React.createRef();
  }

  render() {
    const { selectedOption, selectOptionCallback } = this.props;

    return (
      <div ref={this.optionsRef} className="aura-form-items-link-card-options">
        <AuraFormFieldItem
          selectedOption={selectedOption}
          selectOptionCallback={selectOptionCallback}
          fieldKey="mobile"
          fieldValue="mobile"
          fieldText={getStringMessage('mobile_label')}
        />
        <AuraFormFieldItem
          selectedOption={selectedOption}
          selectOptionCallback={selectOptionCallback}
          fieldKey="card"
          fieldValue="cardNumber"
          fieldText={getStringMessage('aura_account_number_label')}
        />
        <AuraFormFieldItem
          selectedOption={selectedOption}
          selectOptionCallback={selectOptionCallback}
          fieldKey="email"
          fieldValue="email"
          fieldText={getStringMessage('email_address')}
        />
      </div>
    );
  }
}

export default AuraFormFieldOptions;
