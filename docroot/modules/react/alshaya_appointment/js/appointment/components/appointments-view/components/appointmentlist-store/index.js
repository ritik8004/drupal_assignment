import React from 'react';
import { fetchAPIData } from '../../../../../utilities/api/fetchApiData';
import StoreAddress from '../../../appointment-store/components/store-address';

export default class AppointmentListStore extends React.Component {
  constructor(props) {
    super(props);
    const { appointment } = this.props;
    this.state = {
      locationData: {},
      appointment,
    };
  }

  componentDidMount() {
    const { appointment } = this.state;
    const { locationExternalId } = appointment;

    if (locationExternalId) {
      const apiUrl = `/get/store/criteria?location=${locationExternalId}`;
      const apiData = fetchAPIData(apiUrl);
      if (apiData instanceof Promise) {
        apiData.then((result) => {
          if (result.error === undefined && result.data !== undefined) {
            this.setState({
              locationData: result.data.return.locations,
            });
          }
        });
      }
    }
  }

  render() {
    let address = {};
    let storeName = '';
    const { locationData } = this.state;
    if (locationData !== undefined) {
      address = locationData.companyAddress;
      storeName = locationData.locationName;
    }

    return (
      <div className="appointment-list-store">
        <span>{ Drupal.t('Store Location') }</span>
        <div>
          <p>{storeName}</p>
          <StoreAddress address={address} />
        </div>
      </div>
    );
  }
}
