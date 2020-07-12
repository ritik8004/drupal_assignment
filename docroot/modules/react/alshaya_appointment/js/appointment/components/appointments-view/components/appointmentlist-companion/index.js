import React from 'react';
import { fetchAPIData } from '../../../../../utilities/api/fetchApiData';

export default class AppointmentListCompanions extends React.Component {
  constructor(props) {
    super(props);
    const { appointment } = this.props;
    this.state = {
      companionData: {},
      appointment,
    };
  }

  componentDidMount() {
    const { appointment } = this.state;
    const { confirmationNumber } = appointment;
    const { id } = window.drupalSettings.alshaya_appointment.user_details;

    if (confirmationNumber) {
      const apiUrl = `/get/companions?appointment=${confirmationNumber}&id=${id}`;
      const apiData = fetchAPIData(apiUrl);
      if (apiData instanceof Promise) {
        apiData.then((result) => {
          if (result.error === undefined && result.data !== undefined) {
            this.setState({
              companionData: result.data.return,
            });
          }
        });
      }
    }
  }

  render() {
    const companions = [];
    let companionsRender = [];
    const { companionData } = this.state;
    if (companionData !== undefined && companionData.length > 0) {
      let k = 0;
      for (let i = 0; i < companionData.length; i++) {
        const item = companionData[i];
        if (item.question.includes('First')) {
          if (Object.prototype.hasOwnProperty.call(item, 'answer')) {
            companions[k] = {
              firstName: item.answer,
              lastName: '',
              dob: '',
            };
          } else {
            companions[k] = {
              firstName: '',
              lastName: '',
              dob: '',
            };
          }
        }
        if (item.question.includes('Last')) {
          if (Object.prototype.hasOwnProperty.call(item, 'answer')) {
            companions[k] = {
              firstName: companions[k].firstName,
              lastName: item.answer,
              dob: '',
            };
          } else {
            companions[k] = {
              firstName: companions[k].firstName,
              lastName: '',
              dob: '',
            };
          }
        }
        if (item.question.includes('Date')) {
          if (Object.prototype.hasOwnProperty.call(item, 'answer')) {
            companions[k] = {
              firstName: companions[k].firstName,
              lastName: companions[k].lastName,
              dob: item.answer,
            };
          } else {
            companions[k] = {
              firstName: companions[k].firstName,
              lastName: companions[k].lastName,
              dob: '',
            };
          }
          k += 1;
        }
      }

      companionsRender = companions.map((item) => (
        <div>
          <span>{item.firstName}</span>
          <span>{item.lastName}</span>
        </div>
      ));
    }

    return (
      <div className="appointment-list-companion">
        <span>
          {Drupal.t('Customer Details')}
        </span>
        { companionsRender }
      </div>
    );
  }
}
