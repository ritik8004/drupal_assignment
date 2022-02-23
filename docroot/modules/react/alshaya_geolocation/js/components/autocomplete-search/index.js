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
    const { searchStores } = this.props;
    return (
      <>
        <Autocomplete
          apiKey="AIzaSyBL9faHw5s_vO1sUalcbQv05dzce_71fUY"
          onPlaceSelected={(place) => {
            searchStores(place);
          }}
          options={{
            types: ['(regions)'],
            componentRestrictions: { country: 'kw' },
          }}
        />
      </>
    );
  }
}
