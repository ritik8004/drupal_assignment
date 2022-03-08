import React from 'react';
import AutocompleteSearch from '../components/autocomplete-search';
import SingleMarkerMap from '../components/MapContainer/single-marker';
import MultipeMarkerMap from '../components/MapContainer/multiple-marker';
import { ListItem } from '../components/ListItem';

export class StoreFinder extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      stores: [],
      count: 1,
      showSpecificPlace: false,
      showListingView: true,
      showMapView: false,
      specificPlace: {},
      center: {},
      zoom: 10,
      groupedStores: [],
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
          latitude: '29.302761',
          longitude: '47.940268',
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
    const storeSort = (a, b) => (a.store_name.toLowerCase() > b.store_name.toLowerCase() ? 1 : -1);
    stores.items.sort(storeSort);
    const prevState = this.state;
    this.setState(
      {
        ...prevState,
        stores: stores.items,
        count: stores.total_count,
        center: { lat: stores.items[0].latitude, lng: stores.items[0].longitude },
      },
      () => {
        const currentState = this.state;
        const obj = currentState.stores.reduce((acc, c) => {
          const letter = c.store_name[0];
          acc[letter] = (acc[letter] || []).concat({ id: c.id, store_name: c.store_name });
          return acc;
        }, {});
        this.setState({
          groupedStores: obj,
        });
      },
    );
  }

  showSpecificPlace = (id) => {
    const btn = document.querySelector('.gm-ui-hover-effect');
    if (btn) {
      btn.click();
    }
    const { stores } = this.state;
    const specificPlace = stores.filter((obj) => obj.id === id);
    this.setState({
      showSpecificPlace: true,
      specificPlace: specificPlace[0],
      showingInfoWindow: false,
      activeMarker: null,
    });
  }

  hideSpecificPlace = () => {
    const btn = document.querySelector('.gm-ui-hover-effect');
    if (btn) {
      btn.click();
    }
    this.setState({
      showSpecificPlace: false,
      specificPlace: {},
      showingInfoWindow: false,
      activeMarker: null,
    });
  }

  showListingView = () => {
    window.location.href = 'store-finder';
  }

  showMapView = () => {
    this.setState({
      showListingView: false,
      showMapView: true,
    });
  }

  searchStores = (place) => {
    const currentLocation = JSON.parse(JSON.stringify(place.geometry.location));
    const { stores } = this.state;
    const nearbyStores = this.nearByStores(stores, currentLocation);
    const prevState = this.state;
    this.setState({ ...prevState, stores: nearbyStores, count: nearbyStores.length });
    window.location.href = `store-finder/list?latitude=${currentLocation.lat}&longitude=${currentLocation.lng}`;
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
      window.location.href = `/store-finder/list?latitude=${currentLocation.lat}&longitude=${currentLocation.lng}`;
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

  showAllStores = () => {
    window.location.href = '/store-finder/';
  }

  render() {
    const {
      stores,
      showSpecificPlace,
      specificPlace,
      center,
      showListingView,
      showMapView,
      zoom,
      groupedStores,
    } = this.state;
    const seperate = Math.ceil(Object.keys(groupedStores).length / 2);
    const firstColumn = Object.entries(groupedStores).slice(0, seperate);
    const secondColumn = Object.entries(groupedStores).slice(seperate);
    return (
      <>
        <div className="l-container">
          {showSpecificPlace
            ? (
              <div>
                <div onClick={this.findNearMe}>{Drupal.t('Near me')}</div>
                <AutocompleteSearch searchStores={(place) => this.searchStores(place)} />
                <div onClick={this.showAllStores}>{Drupal.t('List all H&M stores')}</div>
              </div>
            ) : (
              <div>
                <div onClick={this.findNearMe}>{Drupal.t('Near me')}</div>
                <AutocompleteSearch searchStores={(place) => this.searchStores(place)} />
                <div onClick={this.showListingView}>{Drupal.t('Show Listing View')}</div>
                <div onClick={this.showMapView}>{Drupal.t('Show Map View')}</div>
              </div>
            ) }
        </div>
        {showSpecificPlace
          ? (
            <div className="individual--store">
              <div className="view-content">
                <div className="list-view-locator">
                  <div className="back-link">
                    <a href="#" onClick={this.hideSpecificPlace}>Back</a>
                  </div>
                  <ListItem specificPlace={specificPlace} />
                </div>
              </div>
              <div className="view-content" style={{ height: '500px' }}>
                <SingleMarkerMap store={specificPlace} center={center} />
              </div>
            </div>
          )
          : (
            <div className="l-container">
              {showListingView
              && (
                <div>
                  <div className="view-content">
                    <div>select a store to see details</div>
                    <div className="warpper">
                      <div className="col-1">
                        {firstColumn.map((value) => (
                          <div key={value[0]}>
                            <div>{value[0]}</div>
                            <div>
                              {value[1].map((item) => (
                                <div
                                  key={item.id}
                                  onClick={() => this.showSpecificPlace(item.id)}
                                >
                                  {item.store_name}
                                </div>
                              ))}
                            </div>
                          </div>
                        ))}
                      </div>
                      {secondColumn
                      && (
                      <div className="col-2">
                        {secondColumn.map((value) => (
                          <div key={value[0]}>
                            <div>{value[0]}</div>
                            <div>
                              {value[1].map((item) => (
                                <div
                                  key={item.id}
                                  onClick={() => this.showSpecificPlace(item.id)}
                                >
                                  {item.store_name}
                                </div>
                              ))}
                            </div>
                          </div>
                        ))}
                      </div>
                      )}
                    </div>
                  </div>
                </div>
              )}
              {showMapView
              && (
                <div className="view-content" style={{ height: '500px' }}>
                  <MultipeMarkerMap center={center} zoom={zoom} stores={stores} />
                </div>
              )}
            </div>
          )}
      </>
    );
  }
}
export default StoreFinder;
