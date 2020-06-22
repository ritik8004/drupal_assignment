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
  const WSDL_CONFIGURATION_SERVICES_URL = 'https://api-stage.timetradesystems.co.uk/soap/ConfigurationServices?wsdl';

  /**
   * WSDL appointment service url.
   */
  const WSDL_APPOINTMENT_SERVICES_URL = 'https://api-stage.timetradesystems.co.uk/soap/AppointmentServices?wsdl';

  /**
   * WSDL client service url.
   */
  const WSDL_CLIENT_SERVICES_URL = 'https://api-stage.timetradesystems.co.uk/soap/ClientServices?wsdl';

}
