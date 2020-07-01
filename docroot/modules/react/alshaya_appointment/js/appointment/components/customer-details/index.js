import React from 'react';
import { getInputValue } from '../../../utilities/helper';
import { setStorageInfo, getStorageInfo } from '../../../utilities/storage';
import ClientDetails from './components/client-details';
import CompanionDetails from './components/companion-details';
import { processCustomerDetails } from '../../../utilities/validate';

export default class CustomerDetails extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = getStorageInfo();

    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
      };
    }
  }

  handleChange = (e) => {
    const { name } = e.target;
    const value = getInputValue(e);
    this.setState((prevState) => ({
      ...prevState,
      [name]: value,
    }));
  }

  handleSubmit = async (e) => {
    e.preventDefault();
    // Valid form fields.
    const isError = await processCustomerDetails(e);

    if (!isError) {
      setStorageInfo(this.state);
      const { handleSubmit } = this.props;
      handleSubmit();
    }
  }

  render() {
    return (
      <div className="customer-details-wrapper">
        <form
          className="appointment-customer-details-form"
          onSubmit={(e) => this.handleSubmit(e)}
        >
          <ClientDetails
            handleChange={this.handleChange}
          />
          <CompanionDetails
            handleChange={this.handleChange}
          />
          <div className="disclaimer-wrapper">
            {drupalSettings.alshaya_appointment.customer_details_disclaimer_text}
          </div>
          <div className="customer-details-buttons-wrapper">
            <button
              className="customer-details-button"
              type="submit"
            >
              {Drupal.t('Book Appointment')}
            </button>
          </div>
        </form>
      </div>
    );
  }
}
