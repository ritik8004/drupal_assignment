import React from 'react';
import _find from 'lodash/find';
import TextField from '../../../../../utilities/textfield';
import { fetchAPIData } from '../../../../../utilities/api/fetchApiData';
import getStringMessage from '../../../../../../../js/utilities/strings';

export default class CompanionDetails extends React.Component {
  constructor(props) {
    super(props);
    const localStorageValues = Drupal.getItemFromLocalStorage('appointment_data');

    if (localStorageValues) {
      this.state = {
        ...localStorageValues,
        setCounter: false,
      };
    }
  }

  componentDidMount() {
    const {
      selectedStoreItem, appointmentCategory, appointmentType,
    } = this.state;
    const apiUrl = `/get/questions?location=${selectedStoreItem.locationExternalId}&program=${appointmentCategory.id}&activity=${appointmentType.value}`;
    const apiData = fetchAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          this.setState((prevState) => ({
            ...prevState,
            questions: result.data,
          }));
        }
      });
    }
  }

  handleChange = (e) => {
    const { handleChange } = this.props;
    handleChange(e);
  }

  handleAddFields = () => {
    const { handleAddCompanion } = this.props;
    handleAddCompanion();
  };

  handleRemoveFields = (e) => {
    const { handleRemoveCompanion } = this.props;
    handleRemoveCompanion(e);
  };

  render() {
    const {
      questions,
      appointmentCompanionItems,
      appointmentForYou,
      setCounter,
    } = this.state;
    const {
      companionData,
      appointmentCompanion,
    } = this.props;
    const companionLimitReached = (appointmentCompanion.value >= appointmentCompanionItems.length);

    // if there is 1 attendee and appointment for self,
    // then appointment box is not shown, only add button shown.
    if (appointmentCompanion.value === 1 && appointmentForYou === 'Yes' && !setCounter) {
      appointmentCompanion.value = 0;
      this.setState({
        setCounter: true,
      });
    }

    // If more than 1 attendees and appointment for self,
    // then -1 boxes shown, eg 2 box if 3 selected.
    if (appointmentCompanion.value > 1 && appointmentForYou === 'Yes' && !setCounter) {
      appointmentCompanion.value -= 1;
      this.setState({
        setCounter: true,
      });
    }

    const companionQuestions = [...Array(parseInt(appointmentCompanion.value, 10))].map((e, i) => {
      const companionNum = i + 1;
      const companionNamePrefix = `bootscompanion${companionNum}`;
      const firstName = `${companionNamePrefix}name`;
      const lastName = `${companionNamePrefix}lastname`;
      const dob = `${companionNamePrefix}dob`;
      const firstNameData = _find(questions, ['questionExternalId', firstName]);
      const lastNameData = _find(questions, ['questionExternalId', lastName]);
      const dobData = _find(questions, ['questionExternalId', dob]);
      let defaultfirstName; let defaultlastName; let
        defaultdob;

      if (companionData) {
        ({
          [firstName]: defaultfirstName,
          [lastName]: defaultlastName,
          [dob]: defaultdob,
        } = companionData);
      }

      if (firstNameData && lastNameData && dobData) {
        return (
          <div key={companionNum.toString()} className={`companion-details-item ${companionNamePrefix}-details`}>
            <div className="details-header-wrapper">
              <div className="companion-detail-heading">{`${getStringMessage('companion_label')} ${companionNum} ${getStringMessage('details')}`}</div>
              <div className="delete-companion">
                <button
                  className="btn btn-link"
                  type="button"
                  data-companion-id={companionNum}
                  onClick={this.handleRemoveFields}
                >
                  {getStringMessage('delete')}
                </button>
              </div>
            </div>
            <div className="user-details-wrapper">
              <div className="user-detail-name-wrapper">
                <div className="item user-firstname">
                  <TextField
                    type="text"
                    required={firstNameData.required}
                    name={firstName}
                    defaultValue={defaultfirstName}
                    className={firstName !== '' ? 'focus' : ''}
                    label={getStringMessage('first_name_label')}
                    handleChange={this.handleChange}
                  />
                </div>
                <div className="item user-lastname">
                  <TextField
                    type="text"
                    required={lastNameData.required}
                    name={lastName}
                    defaultValue={defaultlastName}
                    className={lastName !== '' ? 'focus' : ''}
                    label={getStringMessage('last_name_label')}
                    handleChange={this.handleChange}
                  />
                </div>
              </div>
              <div className="item user-dob">
                <label>{`${getStringMessage('dob_label')}*`}</label>
                <TextField
                  type="date"
                  required={dobData.required}
                  name={dob}
                  defaultValue={defaultdob}
                  className={dob !== '' ? 'focus' : ''}
                  handleChange={this.handleChange}
                  section="companionData"
                />
              </div>
            </div>
          </div>
        );
      }
      return null;
    });

    return (
      <div className="companion-details-wrapper">
        <div className="companion-details-questions">
          {companionQuestions}
        </div>
        <div className="add-companion">
          {!companionLimitReached && (
          <button
            className="btn btn-link"
            type="button"
            onClick={() => this.handleAddFields()}
          >
            {getStringMessage('add_companion_label')}
          </button>
          )}
        </div>
      </div>
    );
  }
}
