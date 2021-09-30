import React from 'react';
import qs from 'qs';
import { createBrowserHistory } from 'history';
import dispatchCustomEvent from '../../../utilities/events';
import { isExpressDeliveryEnabled } from '../../../../../js/utilities/expressDeliveryHelper';

const history = createBrowserHistory();

export default class WithModal extends React.Component {
  isComponentMounted = false;

  constructor(props) {
    super(props);
    this.key = (!props.modalStatusKey) ? 'open' : props.modalStatusKey;
    this.state = { [this.key]: false };
  }

  componentDidMount() {
    this.isComponentMounted = true;

    if (isExpressDeliveryEnabled()) {
      document.addEventListener('openAddressContentPopup', this.openAddressContentPopUp);
    }
    // Adding custom "escape" key event listner for popup.
    // As, popup module's default event handler triggers "closeModal" props
    // which is passed to <Popup> component. and as we are handling closemodal
    // on custom close button we are passing "closeModal" prop to children
    // component.
    // so on "Escape" button click, it triggers closeModal twice. one
    // for the rendered component and one for <Popup> component. which
    // triggeres history.goback() twice. and which redirect us back to
    // previous page (Instead of just removing recent hash from history).
    window.addEventListener('keyup', this.onEscape);
    window.addEventListener('popstate', this.popstate);
    document.addEventListener('closeModal', this.goBackInHistory);
  }

  componentWillUnmount() {
    this.isComponentMounted = false;
    window.removeEventListener('keyup', this.onEscape);
    window.removeEventListener('popstate', this.popstate);
    document.removeEventListener('closeModal', this.goBackInHistory);
  }

  openAddressContentPopUp = (e) => {
    if (e.detail && this.key === 'hdInfo') {
      this.triggerOpenModal();
    }
  }

  popstate = (e) => {
    const modalKey = !e.state ? null : e.state.state.modal;
    if (this.isComponentMounted) {
      this.triggerCloseModal(modalKey);
    }
  }

  triggerOpenModal = () => {
    history.push({ hash: qs.stringify({ modal: this.key }) }, { modal: this.key });
    this.setState({ [this.key]: true });
  }

  triggerCloseModal = (modalKey) => {
    if (modalKey && this.key === modalKey) {
      return;
    }
    this.setState({ [this.key]: false });
  }

  goBackInHistory = (e) => {
    // We Do not want to close popup, which are not in the given event object
    // When we receive event object from customEventDispatch.
    if (e && e.detail !== this.key) {
      return;
    }
    const { [this.key]: isModalOpen } = this.state;
    const { areaUpdated } = this.props;
    if (e === undefined && areaUpdated && this.key === 'hdInfo') {
      dispatchCustomEvent('openAreaPopupConfirmation', areaUpdated);
    }
    if (isModalOpen) {
      history.goBack();
    }
  }

  onEscape = (e) => {
    if (e.key === 'Escape') this.goBackInHistory();
  }

  render() {
    const { children } = this.props;
    const { [this.key]: isModalOpen } = this.state;
    return (
      <>
        {children({
          triggerOpenModal: this.triggerOpenModal,
          triggerCloseModal: this.goBackInHistory,
          isModalOpen,
        })}
      </>
    );
  }
}
