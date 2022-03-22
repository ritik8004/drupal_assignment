import React from 'react';
import {
  Map, Marker, InfoWindow, GoogleApiWrapper,
} from 'google-maps-react';
import AutocompleteSearch from '../components/autocomplete-search';
import { InfoPopUp } from '../components/MapContainer/InfoPopup';
import { ListItemClick } from '../components/ListItemClick';
import getStringMessage from '../../../../alshaya_spc/js/utilities/strings';
import { getCnCModalContactSubtitle } from '../../../../alshaya_spc/js/utilities/cnc_util';
import collectionPointsEnabled from '../../../../js/utilities/pudoAramaxCollection';
import SectionTitle from '../../../../alshaya_spc/js/utilities/section-title';
import ContactInfoForm from '../../../../alshaya_spc/js/checkout/components/contact-info-form';

export class StoreClickCollect extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      selectedLocation: false,
      stores: [],
      count: 1,
      center: {},
      newCenter: {},
      zoomValue: 10,
      activeMarker: {},
      selectedPlace: {},
      selectedStore: {},
      showingInfoWindow: false,
      detailForm: false,
    };
  }

  componentDidMount() {
    // This will be replace with MDC data api call.
    const stores = {
      items: [
        {
          id: 2,
          store_code: 'RA1-1730-HEN',
          store_name: 'H&M Grand Avenue',
          latitude: '30.302761',
          longitude: '30.940268',
          status: 1,
          website_id: 1,
          pudo_service: 0,
          sts_service: 1,
          sts_delivery_time_label: '1-2 days',
          rnc_service: 1,
          store_phone: '+965 2228 3059',
          store_email: '',
          address: [
            {
              code: 'street',
              value: '5th Ring Road Sheikh Zayed Bin Sultan Al Nahyan Road',
              label: null,
            },
            {
              code: 'governate',
              value: '2',
              label: null,
            },
            {
              code: 'area',
              value: '17',
              label: null,
            },
            {
              code: 'firstname',
              value: '',
              label: null,
            },
            {
              code: 'lastname',
              value: '',
              label: null,
            },
            {
              code: 'telephone',
              value: '',
              label: null,
            },
            {
              code: 'address_block_segment',
              value: '',
              label: null,
            },
            {
              code: 'address_building_segment',
              value: '',
              label: null,
            },
            {
              code: 'address_apartment_segment',
              value: '',
              label: null,
            },
          ],
          store_hours: [
            {
              code: '2',
              value: '10 am - 11 pm ',
              label: 'Sunday',
            },
            {
              code: '5',
              value: '10 am - 11 pm ',
              label: 'Monday',
            },
            {
              code: '8',
              value: '10 am - 11 pm ',
              label: 'Tuesday',
            },
            {
              code: '11',
              value: '10 am - 11 pm ',
              label: 'Wednesday',
            },
            {
              code: '14',
              value: '10 am - 12 pm ',
              label: 'Thursday',
            },
            {
              code: '17',
              value: '10 am - 12 pm ',
              label: 'Friday',
            },
            {
              code: '20',
              value: '10 am - 12 pm ',
              label: 'Saturday',
            },
          ],
          created_at: null,
          modified_at: '2020-10-08 10:23:10',
          store_id: '1',
          included_in_store: '',
        },
        {
          id: 5,
          store_code: 'RA1-1073-HEN',
          store_name: 'Al Bairaq Mall',
          latitude: '29.173542',
          longitude: '48.099457',
          status: 1,
          website_id: 1,
          pudo_service: 0,
          sts_service: 1,
          sts_delivery_time_label: '1-2 days',
          rnc_service: 0,
          store_phone: '+965-22581963',
          store_email: '',
          address: [
            {
              code: 'address_building_segment',
              value: 'Bairaq Mall',
              label: null,
            },
            {
              code: 'street',
              value: 'Block 5',
              label: null,
            },
            {
              code: 'governate',
              value: '74',
              label: null,
            },
            {
              code: 'area',
              value: '101',
              label: null,
            },
          ],
          store_hours: [
            {
              code: '2',
              value: '10 am - 10 pm ',
              label: 'Sunday',
            },
            {
              code: '5',
              value: '10 am - 10 pm ',
              label: 'Monday',
            },
            {
              code: '8',
              value: '10 am - 10 pm ',
              label: 'Tuesday',
            },
            {
              code: '11',
              value: '10 am - 10 pm ',
              label: 'Wednesday',
            },
            {
              code: '14',
              value: '10 am - 10 pm ',
              label: 'Thursday',
            },
            {
              code: '17',
              value: '10 am - 11 pm ',
              label: 'Friday',
            },
            {
              code: '20',
              value: '10 am - 11 pm ',
              label: 'Saturday',
            },
          ],
          created_at: null,
          modified_at: null,
          store_id: '1',
          included_in_store: '',
        },
        {
          id: 8,
          store_code: 'RA1-1329-HEN',
          store_name: 'Souk Sharq',
          latitude: '29.341664',
          longitude: '48.073426',
          status: 1,
          website_id: 1,
          pudo_service: 0,
          sts_service: 1,
          sts_delivery_time_label: '1-2 days',
          rnc_service: 0,
          store_phone: '+965 2221 4807',
          store_email: '',
          address: [
            {
              code: 'address_building_segment',
              value: 'Souk Shark',
              label: null,
            },
            {
              code: 'street',
              value: 'Arabian Guld Street',
              label: null,
            },
            {
              code: 'governate',
              value: '140',
              label: null,
            },
            {
              code: 'area',
              value: '227',
              label: null,
            },
          ],
          store_hours: [
            {
              code: '2',
              value: '10 am - 10 pm',
              label: 'Sunday',
            },
            {
              code: '5',
              value: '10 am - 10 pm',
              label: 'Monday',
            },
            {
              code: '8',
              value: '10 am - 10 pm',
              label: 'Tuesday',
            },
            {
              code: '11',
              value: '10 am - 10 pm',
              label: 'Wednesday',
            },
            {
              code: '14',
              value: '10 am - 11 pm',
              label: 'Thursday',
            },
            {
              code: '17',
              value: '10 am - 11 pm',
              label: 'Friday',
            },
            {
              code: '20',
              value: '10 am - 10 pm',
              label: 'Saturday',
            },
          ],
          created_at: null,
          modified_at: null,
          store_id: '1',
          included_in_store: '',
        },
        {
          id: 14,
          store_code: 'RA1-Q126-HEN',
          store_name: 'Avenues Phase 2',
          latitude: '29.302761',
          longitude: '47.940268',
          status: 1,
          website_id: 1,
          pudo_service: 0,
          sts_service: 1,
          sts_delivery_time_label: '1-2 days',
          rnc_service: 1,
          store_phone: '22283808',
          store_email: '',
          address: [
            {
              code: 'street',
              value: 'Al-Rai 5th Ring Road ',
              label: null,
            },
            {
              code: 'governate',
              value: '2',
              label: null,
            },
            {
              code: 'area',
              value: '17',
              label: null,
            },
            {
              code: 'firstname',
              value: '',
              label: null,
            },
            {
              code: 'lastname',
              value: '',
              label: null,
            },
            {
              code: 'telephone',
              value: '',
              label: null,
            },
            {
              code: 'address_block_segment',
              value: '',
              label: null,
            },
            {
              code: 'address_building_segment',
              value: 'Sheikh Zayed Bin Sultan Al Nahyan Road',
              label: null,
            },
            {
              code: 'address_apartment_segment',
              value: '',
              label: null,
            },
          ],
          store_hours: [
            {
              code: '2',
              value: '10 am - 11 pm',
              label: 'Sunday',
            },
            {
              code: '5',
              value: '10 am - 11 pm',
              label: 'Monday',
            },
            {
              code: '8',
              value: '10 am - 11 pm',
              label: 'Tuesday',
            },
            {
              code: '11',
              value: '10 am - 11 pm',
              label: 'Wednesday',
            },
            {
              code: '14',
              value: '10 am - 12 pm',
              label: 'Thursday',
            },
            {
              code: '17',
              value: '10 am - 12 pm',
              label: 'Friday',
            },
            {
              code: '20',
              value: '10 am - 12 pm',
              label: 'Saturday',
            },
          ],
          created_at: null,
          modified_at: '2019-09-23 09:27:10',
          store_id: '1',
          included_in_store: '',
        },
        {
          id: 17,
          store_code: 'RA1-Q004-HEN',
          store_name: 'Fahaheel Building',
          latitude: '29.080532',
          longitude: '48.138647',
          status: 1,
          website_id: 1,
          pudo_service: 0,
          sts_service: 0,
          sts_delivery_time_label: '1-2 days',
          rnc_service: 0,
          store_phone: '+965-22081330',
          store_email: 'hmfahaheel@alshaya.com',
          address: [
            {
              code: 'address_building_segment',
              value: 'Al Shaya Building',
              label: null,
            },
            {
              code: 'street',
              value: 'Al Dabous Street opp. Lulu center',
              label: null,
            },
            {
              code: 'governate',
              value: '74',
              label: null,
            },
            {
              code: 'area',
              value: '95',
              label: null,
            },
            {
              code: 'firstname',
              value: '',
              label: null,
            },
            {
              code: 'lastname',
              value: '',
              label: null,
            },
            {
              code: 'telephone',
              value: '',
              label: null,
            },
            {
              code: 'address_block_segment',
              value: '',
              label: null,
            },
            {
              code: 'address_apartment_segment',
              value: '',
              label: null,
            },
          ],
          store_hours: [
            {
              code: '2',
              value: '10 am - 10 pm ',
              label: 'Sunday',
            },
            {
              code: '5',
              value: '10 am - 10 pm ',
              label: 'Monday',
            },
            {
              code: '8',
              value: '10 am - 10 pm ',
              label: 'Tuesday',
            },
            {
              code: '11',
              value: '10 am - 10 pm ',
              label: 'Wednesday',
            },
            {
              code: '14',
              value: '10 am - 10 pm ',
              label: 'Thursday',
            },
            {
              code: '17',
              value: '10 am - 10 pm ',
              label: 'Friday',
            },
            {
              code: '20',
              value: '10 am - 10 pm ',
              label: 'Saturday',
            },
          ],
          created_at: null,
          modified_at: '2018-10-11 08:32:13',
          store_id: '1',
          included_in_store: '',
        },
        {
          id: 20,
          store_code: 'RA1-1603-HEN',
          store_name: 'Awtad Mall 360',
          latitude: '29.348289',
          longitude: '47.673600',
          status: 1,
          website_id: 1,
          pudo_service: 0,
          sts_service: 1,
          sts_delivery_time_label: '1-2 days',
          rnc_service: 0,
          store_phone: 'No direct line',
          store_email: '',
          address: [
            {
              code: 'address_building_segment',
              value: 'Al Gahs Building 32',
              label: null,
            },
            {
              code: 'street',
              value: 'Block 3',
              label: null,
            },
            {
              code: 'governate',
              value: '332',
              label: null,
            },
            {
              code: 'area',
              value: '356',
              label: null,
            },
          ],
          store_hours: [
            {
              code: '2',
              value: '10 am - 10 pm ',
              label: 'Sunday',
            },
            {
              code: '5',
              value: '10 am - 10 pm ',
              label: 'Monday',
            },
            {
              code: '8',
              value: '10 am - 10 pm ',
              label: 'Tuesday',
            },
            {
              code: '11',
              value: '10 am - 10 pm ',
              label: 'Wednesday',
            },
            {
              code: '14',
              value: '10 am - 10 pm ',
              label: 'Thursday',
            },
            {
              code: '17',
              value: '2 pm - 10 pm ',
              label: 'Friday',
            },
            {
              code: '20',
              value: '10 am - 10 pm ',
              label: 'Saturday',
            },
          ],
          created_at: null,
          modified_at: null,
          store_id: '1',
          included_in_store: '',
        },
        {
          id: 23,
          store_code: 'RA1-1591-HEN',
          store_name: "Marina's Mall",
          latitude: '29.339124',
          longitude: '48.066124',
          status: 1,
          website_id: 1,
          pudo_service: 0,
          sts_service: 1,
          sts_delivery_time_label: '1-2 days',
          rnc_service: 0,
          store_phone: '+965- 22214732 /+965- 22214717',
          store_email: 'hmmarina.kwt@alshaya.com',
          address: [
            {
              code: 'address_building_segment',
              value: 'Marina " Mall',
              label: null,
            },
            {
              code: 'street',
              value: "Arabia'n $ Guld  & Street",
              label: null,
            },
            {
              code: 'governate',
              value: '245',
              label: null,
            },
            {
              code: 'area',
              value: '281',
              label: null,
            },
            {
              code: 'firstname',
              value: '',
              label: null,
            },
            {
              code: 'lastname',
              value: '',
              label: null,
            },
            {
              code: 'telephone',
              value: '',
              label: null,
            },
            {
              code: 'address_block_segment',
              value: '',
              label: null,
            },
            {
              code: 'address_apartment_segment',
              value: '',
              label: null,
            },
          ],
          store_hours: [
            {
              code: '2',
              value: '10 am - 10 pm ',
              label: 'Sunday',
            },
            {
              code: '5',
              value: '10 am - 10 pm ',
              label: 'Monday',
            },
            {
              code: '8',
              value: '10 am - 10 pm ',
              label: 'Tuesday',
            },
            {
              code: '11',
              value: '10 am - 10 pm ',
              label: 'Wednesday',
            },
            {
              code: '14',
              value: '10 am - 11 pm ',
              label: 'Thursday',
            },
            {
              code: '17',
              value: '10 am - 11 pm ',
              label: 'Friday',
            },
            {
              code: '20',
              value: '10 am - 10 pm ',
              label: 'Saturday',
            },
          ],
          created_at: null,
          modified_at: '2018-11-21 13:22:32',
          store_id: '1',
          included_in_store: '',
        },
        {
          id: 26,
          store_code: 'RA1-1116-HEN',
          store_name: 'Salmiya High-street',
          latitude: '29.341755',
          longitude: '48.073499',
          status: 1,
          website_id: 1,
          pudo_service: 0,
          sts_service: 1,
          sts_delivery_time_label: '1-2 days',
          rnc_service: 0,
          store_phone: '22081115',
          store_email: '',
          address: [
            {
              code: 'address_building_segment',
              value: 'Building 3',
              label: null,
            },
            {
              code: 'street',
              value: 'Salem Al Mubarak Street, Block (71)',
              label: null,
            },
            {
              code: 'governate',
              value: '245',
              label: null,
            },
            {
              code: 'area',
              value: '281',
              label: null,
            },
            {
              code: 'firstname',
              value: '',
              label: null,
            },
            {
              code: 'lastname',
              value: '',
              label: null,
            },
            {
              code: 'telephone',
              value: '',
              label: null,
            },
            {
              code: 'address_block_segment',
              value: '',
              label: null,
            },
            {
              code: 'address_apartment_segment',
              value: '',
              label: null,
            },
          ],
          store_hours: [
            {
              code: '2',
              value: '10:00 am - 10:00 pm',
              label: 'Sunday',
            },
            {
              code: '5',
              value: '10:00 am - 10:00 pm',
              label: 'Monday',
            },
            {
              code: '8',
              value: '10:00 am - 10:00 pm',
              label: 'Tuesday',
            },
            {
              code: '11',
              value: '10:00 am - 10:00 pm',
              label: 'Wednesday',
            },
            {
              code: '14',
              value: '10:00  am - 11:00 am',
              label: 'Thursday',
            },
            {
              code: '17',
              value: '10:00  am - 11:00 am',
              label: 'Friday',
            },
            {
              code: '20',
              value: '10:00  am - 11:00 am',
              label: 'Saturday',
            },
          ],
          created_at: null,
          modified_at: '2019-05-15 11:12:36',
          store_id: '1',
          included_in_store: '',
        },
        {
          id: 29,
          store_code: 'RA1-1589-HEN',
          store_name: 'Gate Mall',
          latitude: '29.174665',
          longitude: '48.099054',
          status: 1,
          website_id: 1,
          pudo_service: 0,
          sts_service: 0,
          sts_delivery_time_label: '1-2 days',
          rnc_service: 1,
          store_phone: '+965-220801208',
          store_email: '',
          address: [
            {
              code: 'address_building_segment',
              value: 'Complex 12',
              label: null,
            },
            {
              code: 'street',
              value: 'Road 30, Block 5',
              label: null,
            },
            {
              code: 'governate',
              value: '74',
              label: null,
            },
            {
              code: 'area',
              value: '101',
              label: null,
            },
            {
              code: 'firstname',
              value: '',
              label: null,
            },
            {
              code: 'lastname',
              value: '',
              label: null,
            },
            {
              code: 'telephone',
              value: '',
              label: null,
            },
            {
              code: 'address_block_segment',
              value: '',
              label: null,
            },
            {
              code: 'address_apartment_segment',
              value: '',
              label: null,
            },
          ],
          store_hours: [
            {
              code: '2',
              value: '10 am - 10 pm ',
              label: 'Sunday',
            },
            {
              code: '5',
              value: '10 am - 10 pm ',
              label: 'Monday',
            },
            {
              code: '8',
              value: '10 am - 10 pm ',
              label: 'Tuesday',
            },
            {
              code: '11',
              value: '10 am - 10 pm ',
              label: 'Wednesday',
            },
            {
              code: '14',
              value: '10 am - 11 pm ',
              label: 'Thursday',
            },
            {
              code: '17',
              value: '10 am - 11 pm ',
              label: 'Friday',
            },
            {
              code: '20',
              value: '10 am - 10 pm ',
              label: 'Saturday',
            },
          ],
          created_at: null,
          modified_at: '2019-09-24 08:50:58',
          store_id: '1',
          included_in_store: '',
        },
        {
          id: 32,
          store_code: 'RA1-1039-HEN',
          store_name: 'Old Salmiya',
          latitude: '29.334727',
          longitude: '48.061142',
          status: 1,
          website_id: 1,
          pudo_service: 0,
          sts_service: 1,
          sts_delivery_time_label: '1-2 days',
          rnc_service: 0,
          store_phone: '22081084/22081360',
          store_email: '',
          address: [
            {
              code: 'address_building_segment',
              value: 'Building No 61',
              label: null,
            },
            {
              code: 'street',
              value: 'Salem Al Mubarak Street, Block (21)',
              label: null,
            },
            {
              code: 'governate',
              value: '245',
              label: null,
            },
            {
              code: 'area',
              value: '281',
              label: null,
            },
          ],
          store_hours: [
            {
              code: '2',
              value: '10 am - 10 pm ',
              label: 'Sunday',
            },
            {
              code: '5',
              value: '10 am - 10 pm ',
              label: 'Monday',
            },
            {
              code: '8',
              value: '10 am - 10 pm ',
              label: 'Tuesday',
            },
            {
              code: '11',
              value: '10 am - 10 pm ',
              label: 'Wednesday',
            },
            {
              code: '14',
              value: '10 am - 11 pm ',
              label: 'Thursday',
            },
            {
              code: '17',
              value: '10 am - 11 pm ',
              label: 'Friday',
            },
            {
              code: '20',
              value: '10 am - 10 pm ',
              label: 'Saturday',
            },
          ],
          created_at: null,
          modified_at: null,
          store_id: '1',
          included_in_store: '',
        },
        {
          id: 35,
          store_code: 'RA1-Q035-HEN',
          store_name: 'Al Kout Mall H&M store',
          latitude: '29.078224',
          longitude: '48.137417',
          status: 1,
          website_id: 1,
          pudo_service: 0,
          sts_service: 1,
          sts_delivery_time_label: '1-2 days',
          rnc_service: 0,
          store_phone: '+965 22081427',
          store_email: '',
          address: [
            {
              code: 'firstname',
              value: '',
              label: null,
            },
            {
              code: 'lastname',
              value: '',
              label: null,
            },
            {
              code: 'telephone',
              value: '',
              label: null,
            },
            {
              code: 'address_block_segment',
              value: ' Block 12',
              label: null,
            },
            {
              code: 'street',
              value: 'Dabous ST',
              label: null,
            },
            {
              code: 'address_building_segment',
              value: '85',
              label: null,
            },
            {
              code: 'address_apartment_segment',
              value: '',
              label: null,
            },
            {
              code: 'governate',
              value: '74',
              label: null,
            },
            {
              code: 'area',
              value: '95',
              label: null,
            },
          ],
          store_hours: [
            {
              code: '2',
              value: '10AM - 10PM',
              label: 'Sunday',
            },
            {
              code: '5',
              value: '10AM - 10PM',
              label: 'Monday',
            },
            {
              code: '8',
              value: '10AM - 10PM',
              label: 'Tuesday',
            },
            {
              code: '11',
              value: '10AM - 10PM',
              label: 'Wednesday',
            },
            {
              code: '14',
              value: '10AM - 11PM',
              label: 'Thursday',
            },
            {
              code: '17',
              value: '10AM - 11PM',
              label: 'Friday',
            },
            {
              code: '20',
              value: '10AM - 11PM',
              label: 'Saturday',
            },
          ],
          created_at: null,
          modified_at: '2020-11-24 21:28:33',
          store_id: '1',
          included_in_store: '',
        },
        {
          id: 303,
          store_code: 'RA1-Q237-HEN',
          store_name: 'H&M Avenues Phase 1',
          latitude: '29.302761',
          longitude: '47.940268',
          status: 1,
          website_id: 1,
          pudo_service: 0,
          sts_service: 1,
          sts_delivery_time_label: '1-2 days',
          rnc_service: 0,
          store_phone: '22283901',
          store_email: 'hm.aveflagship-kwt@alshaya.com',
          address: [
            {
              code: 'firstname',
              value: '',
              label: null,
            },
            {
              code: 'lastname',
              value: '',
              label: null,
            },
            {
              code: 'telephone',
              value: '',
              label: null,
            },
            {
              code: 'address_block_segment',
              value: '',
              label: null,
            },
            {
              code: 'street',
              value: 'The Avenues Mall,  Opposite Zara, Beside River Island ',
              label: null,
            },
            {
              code: 'address_building_segment',
              value: '',
              label: null,
            },
            {
              code: 'address_apartment_segment',
              value: '',
              label: null,
            },
            {
              code: 'governate',
              value: '2',
              label: null,
            },
            {
              code: 'area',
              value: '17',
              label: null,
            },
          ],
          store_hours: [
            {
              code: '2',
              value: '10 am - 11 pm ',
              label: 'Sunday',
            },
            {
              code: '5',
              value: '10 am - 11 pm ',
              label: 'Monday',
            },
            {
              code: '8',
              value: '10 am - 11 pm ',
              label: 'Tuesday',
            },
            {
              code: '11',
              value: '10 am - 11 pm ',
              label: 'Wednesday',
            },
            {
              code: '14',
              value: '10 am - 11 pm ',
              label: 'Thursday',
            },
            {
              code: '17',
              value: '10 am - 11 pm ',
              label: 'Friday',
            },
            {
              code: '20',
              value: '10 am - 11 pm ',
              label: 'Saturday',
            },
          ],
          created_at: '2018-09-16 10:21:29',
          modified_at: '2018-09-16 11:23:25',
          store_id: '1',
          included_in_store: '',
        },
        {
          id: 313,
          store_code: 'RA1-Q200-HEN',
          store_name: 'Avenue 4 ( Forum)',
          latitude: '29.302761',
          longitude: '47.940268',
          status: 1,
          website_id: 1,
          pudo_service: 0,
          sts_service: 1,
          sts_delivery_time_label: '1 - 2 days',
          rnc_service: 0,
          store_phone: '(+965) 22283458',
          store_email: 'hmadmin.avenues4-kwt@alshaya.com',
          address: [
            {
              code: 'firstname',
              value: '',
              label: null,
            },
            {
              code: 'lastname',
              value: '',
              label: null,
            },
            {
              code: 'telephone',
              value: '(+965) 22283458',
              label: null,
            },
            {
              code: 'address_block_segment',
              value: '',
              label: null,
            },
            {
              code: 'street',
              value: 'Avenues phase 4 above Tekzone',
              label: null,
            },
            {
              code: 'address_building_segment',
              value: '',
              label: null,
            },
            {
              code: 'address_apartment_segment',
              value: '',
              label: null,
            },
            {
              code: 'governate',
              value: '2',
              label: null,
            },
            {
              code: 'area',
              value: '17',
              label: null,
            },
          ],
          store_hours: [
            {
              code: '2',
              value: '10 am - 11 pm ',
              label: 'Sunday',
            },
            {
              code: '5',
              value: '10 am - 11 pm ',
              label: 'Monday',
            },
            {
              code: '8',
              value: '10 am - 11 pm ',
              label: 'Tuesday',
            },
            {
              code: '11',
              value: '10 am - 11 pm ',
              label: 'Wednesday',
            },
            {
              code: '14',
              value: '10 am - 11 pm ',
              label: 'Thursday',
            },
            {
              code: '17',
              value: '10 am - 11 pm ',
              label: 'Friday',
            },
            {
              code: '20',
              value: '10 am - 11 pm ',
              label: 'Saturday',
            },
          ],
          created_at: '2019-01-02 09:39:49',
          modified_at: '2019-01-03 11:51:23',
          store_id: '1',
          included_in_store: '',
        },
        {
          id: 402,
          store_code: 'RA1-1044-BOD ',
          store_name: 'Al Plaza',
          latitude: '48.011169',
          longitude: '29.346425',
          status: 1,
          website_id: 1,
          pudo_service: 0,
          sts_service: 1,
          sts_delivery_time_label: '',
          rnc_service: 0,
          store_phone: '96522081091',
          store_email: 'bodhaw.kwt@alshaya.com',
          address: [
            {
              code: 'firstname',
              value: 'Test123',
              label: null,
            },
            {
              code: 'lastname',
              value: 'Test123',
              label: null,
            },
            {
              code: 'telephone',
              value: '96522081091',
              label: null,
            },
            {
              code: 'address_block_segment',
              value: '',
              label: null,
            },
            {
              code: 'street',
              value: 'Al Paza Complex, Kuwait',
              label: null,
            },
            {
              code: 'address_building_segment',
              value: '',
              label: null,
            },
            {
              code: 'address_apartment_segment',
              value: '',
              label: null,
            },
            {
              code: 'governate',
              value: '140',
              label: null,
            },
            {
              code: 'area',
              value: '263',
              label: null,
            },
          ],
          store_hours: [
            {
              code: '2',
              value: '10AM TO 10PM',
              label: 'Sunday',
            },
            {
              code: '5',
              value: '10AM TO 10PM',
              label: 'Monday',
            },
            {
              code: '8',
              value: '10AM TO 10PM',
              label: 'Tuesday',
            },
            {
              code: '11',
              value: '10AM TO 10PM',
              label: 'Wednesday',
            },
            {
              code: '14',
              value: '10AM TO 10PM',
              label: 'Thursday',
            },
            {
              code: '17',
              value: '3PM TO 10PM',
              label: 'Friday',
            },
            {
              code: '20',
              value: '3PM TO 10PM',
              label: 'Saturday',
            },
          ],
          created_at: '2021-08-23 11:52:39',
          modified_at: '2021-08-23 11:52:39',
          store_id: '1',
          included_in_store: '',
        },
      ],
      search_criteria: {
        filter_groups: [
          {
            filters: [
              {
                field: 'status',
                value: '1',
                condition_type: 'eq',
              },
            ],
          },
          {
            filters: [
              {
                field: 'store_id',
                value: '1',
                condition_type: '=',
              },
            ],
          },
        ],
      },
      total_count: 14,
    };
    const prevState = this.state;
    this.setState(
      {
        ...prevState,
        stores: stores.items,
        detailForm: false,
        count: stores.total_count,
        center: {
          lat: parseFloat(stores.items[0].latitude),
          lng: parseFloat(stores.items[0].longitude),
        },
        zoomValue: 10,
      },
    );
  }

  onMarkerClick = (props, marker) => {
    const { stores } = this.state;
    const selectedStore = stores.filter((obj) => obj.store_name === props.name);
    this.setState((prevState) => ({
      ...prevState,
      activeMarker: marker,
      selectedPlace: props,
      showingInfoWindow: true,
      newCenter: props.position,
      zoomValue: 10,
      selectedLocation: selectedStore[0],
      selectedStore: {
        name: selectedStore[0].store_name,
        code: selectedStore[0].store_code,
        rnc_available: selectedStore[0].rnc_service,
        cart_address: selectedStore[0].address,
      },
    }));
  };

  onInfoWindowClose = () => this.setState({
    activeMarker: null,
    showingInfoWindow: false,
  });

  showSpecificPlace = (id) => {
    const btn = document.querySelector('.gm-ui-hover-effect');
    if (btn) {
      btn.click();
    }
    const { stores } = this.state;
    const specificPlace = stores.filter((obj) => obj.id === id);
    this.setState({
      selectedLocation: specificPlace[0],
      activeMarker: null,
      newCenter: { lat: specificPlace[0].latitude, lng: specificPlace[0].longitude },
      selectedStore: {
        name: specificPlace[0].store_name,
        code: specificPlace[0].store_code,
        rnc_available: specificPlace[0].rnc_service,
        cart_address: specificPlace[0].address,
      },
    });
  }

  hideSpecificPlace = () => {
    const btn = document.querySelector('.gm-ui-hover-effect');
    if (btn) {
      btn.click();
    }
    this.setState({
      showingInfoWindow: false,
      activeMarker: null,
    });
  }

  searchStores = (place) => {
    const currentLocation = JSON.parse(JSON.stringify(place.geometry.location));
    const { stores } = this.state;
    const nearbyStores = this.nearByStores(stores, currentLocation);
    const prevState = this.state;
    this.setState({
      ...prevState,
      stores: nearbyStores,
      count: nearbyStores.length,
      selectedLocation: {},
    });
  }

  findNearMe = () => {
    if (navigator.geolocation) {
      // Call getCurrentPosition with success and failure callbacks
      navigator.geolocation.getCurrentPosition(this.success, this.fail);
    }
  }

  success = (position) => {
    const currentLocation = { lat: position.coords.longitude, lng: position.coords.latitude };
    const { stores } = this.state;
    const nearbyStores = this.nearByStores(stores, currentLocation);
    if (nearbyStores.length > 0) {
      const prevState = this.state;
      this.setState({ ...prevState, stores: nearbyStores, count: nearbyStores.length });
    }
  }

  fail = () => 'Could not obtain location.';

  nearByStores = (stores, currentLocation) => {
    const nearbyStores = stores.filter((store) => {
      const otherLocation = { lat: +store.latitude, lng: +store.longitude };
      const distance = this.getDistanceBetween(currentLocation, otherLocation);
      return (distance < 5) ? store : null;
    });
    return nearbyStores;
  }

  getDistanceBetween = (location1, location2) => {
    // The math module contains a function
    // named toRadians which converts from
    // degrees to radians.

    const lon1 = (parseInt((location1.lng), 10) * Math.PI) / 180;
    const lon2 = (parseInt((location2.lng), 10) * Math.PI) / 180;
    const lat1 = (parseInt((location1.lat), 10) * Math.PI) / 180;
    const lat2 = (parseInt((location1.lat), 10) * Math.PI) / 180;

    // Haversine formula
    const dlon = lon2 - lon1;
    const dlat = lat2 - lat1;
    const a = (Math.sin(dlat / 2) ** 2)
      + Math.cos(lat1) * Math.cos(lat2)
      * (Math.sin(dlon / 2) ** 2);

    const c = 2 * Math.asin(Math.sqrt(a));
    // Radius of earth in kilometers.
    const r = 6371;
    // calculate the result
    return (c * r);
  }

  sendLocation = (formshow) => {
    this.setState({
      detailForm: formshow,
    });
  }

  render() {
    const {
      stores,
      selectedLocation,
      showingInfoWindow,
      activeMarker,
      selectedPlace,
      selectedStore,
      zoomValue,
      detailForm,
    } = this.state;
    const { newCenter } = this.state;
    const handleScrollTo = () => {
      const container = document.querySelector('.spc-cnc-selected-store-content');
      container.scrollBy({
        top: -300,
        left: 0,
        behavior: 'smooth',
      });
    };
    const { google } = this.props;
    return (
      <>
        <div className="views-content">
          <div className="l-container" style={{ float: 'left' }}>
            <div className="view-content" style={{ height: '500px' }}>
              <Map
                google={google}
                style={{ width: '50%', height: '100%', position: 'relative' }}
                className="map"
                center={newCenter}
                zoom={zoomValue}
              >
                {stores.map((store, index) => (
                  <Marker
                    onClick={this.onMarkerClick}
                    label={(index + 1).toString()}
                    z-index={(index + 1).toString()}
                    key={store.id}
                    title={store.store_name}
                    name={store.store_name}
                    openHours={store.store_hours}
                    address={store.address}
                    position={{ lat: store.latitude, lng: store.longitude }}
                  />
                ))}
                {showingInfoWindow && (
                  <InfoWindow
                    marker={activeMarker}
                    onClose={this.onInfoWindowClose}
                    visible={showingInfoWindow}
                  >
                    <InfoPopUp selectedPlace={selectedPlace} />
                  </InfoWindow>
                )}
              </Map>
            </div>
          </div>
          <div className="l-container" style={{ float: 'right' }}>
            <div id="spc-cnc-address-form-sidebar">
              <div className="str-lst" style={{ display: detailForm ? 'none' : 'block', width: '100%' }}>
                <div className="header">
                  <span>
                    {Drupal.t('Collection store')}
                  </span>
                </div>
                <div>
                  <AutocompleteSearch searchStores={(place) => this.searchStores(place)} />
                  <button type="button" onClick={this.findNearMe}>{Drupal.t('Near me')}</button>
                </div>
                <div>
                  <div className="view-content">
                    <div>{Drupal.t('Find Your Nearest Store')}</div>
                    <div id="click-and-collect-list-view">
                      <ul>
                        {stores.map((store) => (
                          <li>
                            <span
                              key={store.id}
                              className={
                                (selectedLocation.store_name === store.store_name ? 'select-store selected' : 'select-store')
                                }
                              onClick={() => this.showSpecificPlace(store.id)}
                            >
                              {store.store_name}
                            </span>
                          </li>
                        ))}
                      </ul>
                    </div>
                  </div>
                </div>
                <div className={
                  (selectedLocation.store_code ? 'spc-cnc-store-actions show' : 'spc-cnc-store-actions')
                  }
                >
                  <button type="button" onClick={() => this.sendLocation(true)}>
                    {Drupal.t('Select this store')}
                  </button>
                </div>
              </div>
              {detailForm
              && (
                <div id="click-and-collect-selected-store" style={{ display: detailForm ? 'block' : 'none', width: '100%' }}>
                  <div className="spc-cnc-selected-store-header">
                    <span className="spc-cnc-selected-store-back" onClick={() => this.sendLocation(false)} />
                    <SectionTitle>{getStringMessage('cnc_collection_details')}</SectionTitle>
                  </div>
                  <div className="spc-cnc-selected-store-content">
                    <SectionTitle>{getStringMessage(getCnCModalContactSubtitle())}</SectionTitle>
                    <div className="store-details-wrapper">
                      <ListItemClick specificPlace={selectedLocation} />
                      {collectionPointsEnabled() === true
                      && (
                        <div className="spc-cnc-selected-store-pudo-info">
                          {getStringMessage('cnc_contact_info_subtitle')}
                        </div>
                      )}
                    </div>
                    <div className="spc-cnc-contact-form">
                      <ContactInfoForm subTitle={getStringMessage('cnc_contact_info_subtitle')} store={selectedStore} handleScrollTo={handleScrollTo} />
                    </div>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>

      </>
    );
  }
}

export default GoogleApiWrapper({
  apiKey: drupalSettings.alshaya_geolocation.api_key,
})(StoreClickCollect);
