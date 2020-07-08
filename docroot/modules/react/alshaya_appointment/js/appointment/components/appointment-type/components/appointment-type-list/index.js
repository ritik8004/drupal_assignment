import React from 'react';
import ReadMoreAndLess from 'react-read-more-less';
import SectionTitle from '../../../section-title';
import AppointmentSelect from '../appointment-select';

export default class AppointmentTypeList extends React.Component {
  constructor(props) {
    super(props);
    this.state = { activeOption: null, optionState: [] };
  }

  componentDidMount() {
    const { appointmentTypeItems, activeItem } = this.props;
    const options = [];
    if (appointmentTypeItems) {
      appointmentTypeItems.forEach((v, key) => {
        options[key] = {
          value: v.id,
          label: v.name,
        };
      });
    }

    this.setState({
      optionState: options,
    });

    const filterKey = options.filter((v) => activeItem.id === v.value);
    this.updateOption(filterKey);
  }

  onSelectChange = (e, name) => {
    const { onSelectChange } = this.props;
    onSelectChange(e, name);
    this.updateOption([e]);
  }

  updateOption = (filterKey) => {
    this.setState({
      activeOption: filterKey.length ? filterKey[0] : null,
    });
  }

  _getDescription = () => {
    const { appointmentTypeItems, activeItem } = this.props;
    const filterDesc = appointmentTypeItems.filter((v) => activeItem.id === v.id);
    return (
      <>
        <ReadMoreAndLess
          charLimit={250}
          readMoreText={Drupal.t('Read More')}
          readLessText={Drupal.t('Show Less')}
        >
          {filterDesc[0].description}
        </ReadMoreAndLess>
      </>
    );
  }

  render() {
    const { activeItem } = this.props;
    const { activeOption, optionState } = this.state;

    return (
      <div className="appointment-type-list-wrapper appointment-type-item">
        <SectionTitle>
          {Drupal.t('Appointment Type')}
          :*
        </SectionTitle>
        <div className="appointment-type-list-inner-wrapper fadeInUp">
          <AppointmentSelect
            onSelectChange={this.onSelectChange}
            options={optionState}
            activeItem={activeItem}
            aptSelectClass="appointment-type-select"
            name="appointmentType"
            activeOption={activeOption}
          />
          { activeItem
            ? (
              // eslint-disable-next-line no-underscore-dangle
              this._getDescription()
            )
            : null}
        </div>
      </div>
    );
  }
}
