import React from 'react';
import ReadMoreAndLess from 'react-read-more-less';
import SectionTitle from '../../../section-title';
import AppointmentSelect from '../appointment-select';

export default class AppointmentTypeList extends React.Component {
  onSelectChange = (e, name) => {
    const { onSelectChange } = this.props;
    onSelectChange(e, name);
  };

  _getDescription = () => {
    const { appointmentTypeItems, activeItem } = this.props;
    const filterDesc = appointmentTypeItems.filter((v) => activeItem.value === v.id);
    return (
      <>
        {(filterDesc.length
          ? (
            <ReadMoreAndLess
              charLimit={250}
              readMoreText={Drupal.t('Read More')}
              readLessText={Drupal.t('Show Less')}
            >
              {filterDesc[0].description}
            </ReadMoreAndLess>
          )
          : null
        )}
      </>
    );
  };

  render() {
    const { activeItem, appointmentTypeItems } = this.props;

    const options = [];
    if (appointmentTypeItems) {
      appointmentTypeItems.forEach((v, key) => {
        options[key] = {
          value: v.id,
          label: v.name,
        };
      });
    }

    return (
      <div className="appointment-type-list-wrapper appointment-type-item">
        <SectionTitle>
          {Drupal.t('Appointment Type')}
          :*
        </SectionTitle>
        <div className="appointment-type-list-inner-wrapper fadeInUp">
          <AppointmentSelect
            onSelectChange={this.onSelectChange}
            options={options}
            activeItem={activeItem}
            aptSelectClass="appointment-type-select"
            name="appointmentType"
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
