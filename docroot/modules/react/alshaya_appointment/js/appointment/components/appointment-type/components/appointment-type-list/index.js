import React from 'react';
import SectionTitle from '../../../section-title';
import AppointmentSelect from '../appointment-select';
import ReadMore from '../../../../../common/components/readmore';
import getStringMessage from '../../../../../../../js/utilities/strings';

export default class AppointmentTypeList extends React.Component {
  onSelectChange = (e, name) => {
    const { onSelectChange } = this.props;
    onSelectChange(e, name);
  };

  render() {
    const { activeItem, appointmentTypeItems } = this.props;
    let filterDescriptionContent = '';
    if (activeItem) {
      const filterDesc = appointmentTypeItems.filter((v) => activeItem.value === v.id);
      if (filterDesc.length) {
        filterDescriptionContent = filterDesc[0].description;
      }
    }

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
          {getStringMessage('activity_label')}
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
          { filterDescriptionContent.length
            ? (
              <ReadMore description={filterDescriptionContent} />
            )
            : null}
        </div>
      </div>
    );
  }
}
