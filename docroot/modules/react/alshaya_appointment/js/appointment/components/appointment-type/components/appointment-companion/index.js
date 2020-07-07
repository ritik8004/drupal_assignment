import React from 'react';
import SectionTitle from '../../../section-title';
import AppointmentSelect from '../appointment-select';

export default class AppointmentCompanion extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      activeOption: null,
      optionState: [],
    };
  }

  componentDidMount() {
    const { appointmentCompanionItems, activeItem } = this.props;
    const options = [];
    appointmentCompanionItems.forEach((v, key) => {
      options[key] = {
        value: v.value,
        label: v.label,
      };
    });

    this.setState({
      optionState: options,
    });

    const filterKey = options.filter((v) => parseInt(activeItem.id, 10) === v.value);
    const updateFilterKey = filterKey.length ? filterKey : options;
    this.onSelectChange(updateFilterKey[0], 'appointmentCompanion');
  }

  onSelectChange = (e, name) => {
    this.updateOption([e]);
    const { onSelectChange } = this.props;
    onSelectChange(e, name);
  }

  updateOption = (filterKey) => {
    this.setState({
      activeOption: filterKey.length ? filterKey[0] : null,
    });
  }

  render() {
    const { activeItem } = this.props;
    const { activeOption, optionState } = this.state;
    return (
      <div className="appointment-companion-wrapper appointment-type-item">
        <SectionTitle>
          {Drupal.t('How many people do you want to book the appointment for?')}
          *
        </SectionTitle>
        <AppointmentSelect
          options={optionState}
          onSelectChange={this.onSelectChange}
          activeItem={activeItem}
          name="appointmentCompanion"
          aptSelectClass="appointment-companion-select"
          activeOption={activeOption || optionState[0]}
        />
      </div>
    );
  }
}
