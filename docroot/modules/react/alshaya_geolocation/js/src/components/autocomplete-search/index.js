import React from 'react';

const useScript = (selector = 'body', async = true) => {
  if (typeof google !== 'undefined') {
    return;
  }
  const libraries = 'places';
  const apiKey = drupalSettings.alshaya_geolocation.api_key;
  const { currentLanguage } = drupalSettings.path;
  const languageQueryParam = `&language=${currentLanguage}`;
  const googleMapsScriptUrl = `https://maps.googleapis.com/maps/api/js?libraries=${libraries}&key=${apiKey}${languageQueryParam}`;
  const element = document.querySelector(selector);
  const script = document.createElement('script');
  script.src = googleMapsScriptUrl;
  script.async = async;
  element.appendChild(script);
};

export default class AutocompleteSearch extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      stores: {},
    };
  }

  componentDidMount() {
    useScript();
    const prevState = this.state;
    const { stores } = this.props;
    this.setState({ ...prevState, stores });
  }

  render() {
    const {
      searchStores,
      placeholder,
      locationName,
    } = this.props;
    const apiKey = drupalSettings.alshaya_geolocation.api_key;
    const id = 'edit-geolocation-geocoder-google-places-api';
    const { regional } = drupalSettings.alshaya_geolocation;

    let autocompleteInit = false;
    const config = {
      fields: [
        'address_components',
        'geometry.location',
        'place_id',
        'formatted_address',
      ],
      types: [
        'geocode',
      ],
      componentRestrictions: {
        country: regional,
      },
    };

    const onPlaceInput = (e) => {
      const autocompleteInput = e.target;
      if (e.target.value.length >= 2 && !autocompleteInit) {
        // Return here if google is not present.
        if (typeof google === 'undefined') {
          return Drupal.alshayaLogger('error', 'Google has not been found. Make sure your provide apiKey prop.');
        }
        if (!google.maps.places) {
          return Drupal.alshayaLogger('error', 'Google maps places API must be loaded.');
        }

        // Continue initiating the autocomplete.
        const autocomplete = new google.maps.places.Autocomplete(autocompleteInput, config);
        autocomplete.addListener('place_changed', () => {
          const place = autocomplete.getPlace();
          searchStores(place);
          google.maps.event.clearInstanceListeners(autocompleteInput);
        });
        autocompleteInit = true;
      } else if (e.target.value.length < 2) {
        e.preventDefault();
        jQuery('.pac-container').remove();
        google.maps.event.clearInstanceListeners(autocompleteInput);
        autocompleteInit = false;
      }
      return true;
    };

    return (
      <>
        <input
          apiKey={apiKey}
          id={id}
          className="store-location-input pac-target-input geolocation-views-filter-geocoder geolocation-geocoder-google-places-api block-store-finder-form__input__text"
          placeholder={placeholder}
          type="text"
          autoComplete="off"
          onKeyUp={onPlaceInput}
          defaultValue={locationName}
        />
      </>
    );
  }
}
