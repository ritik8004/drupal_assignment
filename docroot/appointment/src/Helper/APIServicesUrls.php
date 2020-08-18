<?php

namespace App\Helper;

/**
 * Class APIServicesUrls.
 *
 * Class contains all the Time Trade API services URLs.
 */
final class APIServicesUrls {
  /**
   * WSDL configuration service url.
   */
  const WSDL_CONFIGURATION_SERVICES_URL = '/soap/ConfigurationServices?wsdl';

  /**
   * WSDL appointment service url.
   */
  const WSDL_APPOINTMENT_SERVICES_URL = '/soap/AppointmentServices?wsdl';

  /**
   * WSDL client service url.
   */
  const WSDL_CLIENT_SERVICES_URL = '/soap/ClientServices?wsdl';

  /**
   * Translation service url to get all translations.
   */
  const TRANSLATION_SERVICE_URL_ALL = '/api/v1/project/get';

  /**
   * Translation service url to get individual translations.
   */
  const TRANSLATION_SERVICE_URL_INDIVIDUAL = '/api/v1/translate/get';

}
