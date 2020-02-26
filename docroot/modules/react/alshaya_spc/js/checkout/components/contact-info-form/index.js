import React from "react";
import Axios from "axios";
import { ClicknCollectContext } from "../../../context/ClicknCollect";
import {
  addShippingInCart,
  removeLoader,
  showLoader
} from "../../../utilities/checkout_util";
import FixedFields from "../fixed-fields";
import { i18nMiddleWareUrl } from "../../../utilities/i18n_url";

class ContactInfoForm extends React.Component {
  static contextType = ClicknCollectContext;

  handleSubmit = (e, store) => {
    e.preventDefault();
    showLoader();
    let form_data = {
      static: {
        firstname: e.target.elements.fname.value,
        lastname: e.target.elements.lname.value,
        email: e.target.elements.email.value,
        telephone: e.target.elements.mobile.value,
        country_id: drupalSettings.country_code
      },
      shipping_type: "cnc",
      store: {
        name: store.name,
        code: store.code,
        rnc_available: store.rnc_available,
        cart_address: store.cart_address
      },
      carrier_info: { ...drupalSettings.cnc.cnc_shipping }
    };

    this.processShippingUpdate(form_data);
  };

  /**
   * Validate mobile number and email address and on success process shipping address update.
   */
  processShippingUpdate = form_data => {
    // Mimic axio request when we don't want to validate email address for existing
    // or recently created customer.
    let customerValidationReuest = new Promise((resolve, reject) => {
      resolve({
        data: {
          exists: false
        }
      });
    });

    if (
      this.context.contactInfo === null ||
      (!this.context.contactInfo &&
        this.context.contactInfo.email !== form_data.static.email)
    ) {
      customerValidationReuest = Axios.get(
        i18nMiddleWareUrl("customer/" + form_data.static.email)
      );
    }

    const mobileValidationRequest = Axios.get(
      Drupal.url("verify-mobile/" + form_data.static.telephone)
    );

    // API call to validate mobile number and email address.
    return Axios.all([mobileValidationRequest, customerValidationReuest])
      .then(
        Axios.spread((mobileValidate, customerEmailValidate) => {
          // Show errors if any, else call update cart api to update shipping address.
          let hasError = this.showMobileAndEmailErrors(
            mobileValidate,
            customerEmailValidate
          );
          if (!hasError) {
            this.updateShipping(form_data);
          } else {
            removeLoader();
          }
        })
      )
      .catch(errors => {
        // React on errors.
      });
  };

  /**
   * Update cart with shipping address.
   */
  updateShipping = form_data => {
    let cart_info = addShippingInCart("update shipping", form_data);
    if (cart_info instanceof Promise) {
      let { updateContactInfo } = this.context;
      cart_info
        .then(cart_result => {
          updateContactInfo(form_data.static);
          let cart_data = {
            cart: cart_result,
            delivery_type: cart_result.delivery_type,
            address: form_data.store.address
          };
          let event = new CustomEvent("refreshCartOnCnCSelect", {
            bubbles: true,
            detail: {
              data: () => cart_data
            }
          });
          document.dispatchEvent(event);
          removeLoader();
        })
        .catch(error => {
          console.error(error);
        });
    }
  };

  /**
   * Show errors if mobile number and customer email is not vaild.
   */
  showMobileAndEmailErrors = (mobileValidate, customerEmailValidate) => {
    // Flag to determine if there any error.
    let isError = false;

    // If invalid mobile number.
    if (mobileValidate.data.status === false) {
      document.getElementById("mobile-error").innerHTML = Drupal.t(
        "Please enter valid mobile number."
      );
      document.getElementById("mobile-error").classList.add("error");
      isError = true;
    } else {
      // Remove error class and any error message.
      document.getElementById("mobile-error").innerHTML = "";
      document.getElementById("mobile-error").classList.remove("error");
    }

    if (customerEmailValidate.data.exists === "wrong") {
      document.getElementById("email-error").innerHTML = Drupal.t(
        "The email address %mail is not valid.",
        {
          "%mail": customerEmailValidate.data.email
        }
      );
      document.getElementById("email-error").classList.add("error");
      isError = true;
    } else if (customerEmailValidate.data.exists === true) {
      document.getElementById("email-error").innerHTML = Drupal.t(
        "Customer already exists."
      );
      document.getElementById("email-error").classList.add("error");
      isError = true;
    } else {
      document.getElementById("email-error").innerHTML = "";
      document.getElementById("email-error").classList.remove("error");
    }

    return isError;
  };

  render() {
    let { store } = this.props;
    let { contactInfo } = this.context;

    return (
      <form
        className="spc-contact-form"
        onSubmit={e => this.handleSubmit(e, store)}
      >
        <FixedFields
          showEmail={true}
          default_val={contactInfo ? { static: contactInfo } : []}
        />
        <div className="spc-address-form-actions">
          <button
            id="save-address"
            className="spc-address-form-submit"
            type="submit"
          >
            {Drupal.t("Save")}
          </button>
        </div>
      </form>
    );
  }
}

export default ContactInfoForm;
