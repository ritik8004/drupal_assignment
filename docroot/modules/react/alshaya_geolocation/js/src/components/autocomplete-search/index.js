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
    return (
      <>
        <Autocomplete
          apiKey={apiKey}
          onPlaceSelected={(place) => {
            searchStores(place);
          }}
          placeholder={placeholder}
          options={{
            types: ['(regions)'],
            componentRestrictions: { country: regional },
          }}
        />
      </>
    );
  }
}
