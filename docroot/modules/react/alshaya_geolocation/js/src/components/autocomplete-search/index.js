import React from 'react';
import Autocomplete from 'react-google-autocomplete';

export default class AutocompleteSearch extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      stores: {},
    };
  }

  componentDidMount() {
    const prevState = this.state;
    const { stores } = this.props;
    this.setState({ ...prevState, stores });
  }

  render() {
    const {
      searchStores,
      placeholder,
    } = this.props;
    const apiKey = drupalSettings.alshaya_geolocation.api_key;
    const { regional } = drupalSettings.alshaya_geolocation;
    const { currentLanguage } = drupalSettings.path;
    return (
      <>
        <Autocomplete
          apiKey={apiKey}
          id="edit-geolocation-geocoder-google-places-api"
          className="store-location-input pac-target-input geolocation-views-filter-geocoder geolocation-geocoder-google-places-api block-store-finder-form__input__text"
          onPlaceSelected={(place) => {
            searchStores(place);
          }}
          placeholder={placeholder}
          type="text"
          language={currentLanguage}
          inputAutocompleteValue="off"
          options={{
            types: ['geocode'],
            componentRestrictions: { country: regional },
          }}
        />
      </>
    );
  }
}
