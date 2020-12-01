<?php

namespace App\Controller\LoyaltyClub;

use App\Service\Magento\MagentoApiWrapper;
use App\Service\Utility;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides route callbacks for different Loyalty Club requirements.
 */
class LoyaltyClubRedeemController {

  /**
   * Magento API Wrapper service.
   *
   * @var \App\Service\Magento\MagentoApiWrapper
   */
  protected $magentoApiWrapper;

  /**
   * Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Utility service.
   *
   * @var \App\Service\Utility
   */
  protected $utility;

  /**
   * LoyaltyClubRedeemController constructor.
   *
   * @param \App\Service\Magento\MagentoApiWrapper $magento_api_wrapper
   *   Magento API wrapper service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger service.
   * @param \App\Service\Utility $utility
   *   Utility Service.
   */
  public function __construct(
    MagentoApiWrapper $magento_api_wrapper,
    LoggerInterface $logger,
    Utility $utility
  ) {
    $this->magentoApiWrapper = $magento_api_wrapper;
    $this->logger = $logger;
    $this->utility = $utility;
  }

  /**
   * Redeem loyalty points.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return success/failure response.
   */
  public function redeemPoints(Request $request) {
    $request_content = json_decode($request->getContent(), TRUE);
    $data = [
      'redeemAuraPoints' => [
        'action' => 'set points',
        'quote_id' => $request_content['cartId'] ?? '',
        'redeem_points' => $request_content['redeemPoints'] ?? '',
        'converted_money_value' => $request_content['moneyValue'] ?? '',
        'currencyCode' => $request_content['currencyCode'] ?? '',
        'payment_method' => 'aura_payment',
      ],
    ];

    // Check if required data is present in request.
    if (empty($data['redeemAuraPoints']['quote_id'])
      || empty($data['redeemAuraPoints']['redeem_points'])
      || empty($data['redeemAuraPoints']['converted_money_value'])
      || empty($data['redeemAuraPoints']['currencyCode'])
      || empty($request_content['cardNumber'])) {
      $message = 'Error while trying to redeem aura points. Quote Id, Redeem Points, Converted Money Value, Currency Code and Card Number is required.';
      $this->logger->error($message . 'Data: @request_data', [
        '@request_data' => json_encode($data),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($message, Response::HTTP_NOT_FOUND));
    }

    try {
      $url = sprintf('apc/%d/redeem-points', $request_content['cardNumber']);
      // $response = $this->magentoApiWrapper->doRequest('POST', $url, ['json' => $data]);
      $responseData = [];

      // @TODO: Remove hard coded response once API starts working.
      $response = [
        "grand_total" => 195.65,
        "base_grand_total" => 225,
        "subtotal" => 195.65,
        "base_subtotal" => 195.65,
        "discount_amount" => 0,
        "base_discount_amount" => 0,
        "subtotal_with_discount" => 195.65,
        "base_subtotal_with_discount" => 195.65,
        "shipping_amount" => 0,
        "base_shipping_amount" => 0,
        "shipping_discount_amount" => 0,
        "base_shipping_discount_amount" => 0,
        "tax_amount" => 29.35,
        "base_tax_amount" => 29.35,
        "weee_tax_applied_amount" => NULL,
        "shipping_tax_amount" => 0,
        "base_shipping_tax_amount" => 0,
        "subtotal_incl_tax" => 225,
        "shipping_incl_tax" => 0,
        "base_shipping_incl_tax" => 0,
        "base_currency_code" => "SAR",
        "quote_currency_code" => "SAR",
        "items_qty" => 1,
        "items" => [
            [
              "item_id" => 19369349,
              "price" => 195.65,
              "base_price" => 195.65,
              "qty" => 1,
              "row_total" => 195.65,
              "base_row_total" => 195.65,
              "row_total_with_discount" => 0,
              "tax_amount" => 29.35,
              "base_tax_amount" => 29.35,
              "tax_percent" => 15,
              "discount_amount" => 0,
              "base_discount_amount" => 0,
              "discount_percent" => 0,
              "price_incl_tax" => 225,
              "base_price_incl_tax" => 225,
              "row_total_incl_tax" => 225,
              "base_row_total_incl_tax" => 225,
              "options" => "[[\"value\" =>\"L\",\"label\" =>\"Size\"]]",
              "weee_tax_applied_amount" => NULL,
              "weee_tax_applied" => NULL,
              "name" => "Logo Shop Cotton Legging",
            ],
        ],
        "total_segments" => [
            [
              "code" => "subtotal",
              "title" => "Subtotal",
              "value" => 225,
            ],
            [
              "code" => "giftwrapping",
              "title" => "Gift Wrapping",
              "value" => NULL,
              "extension_attributes" => [
                "gw_item_ids" => [],
                "gw_price" => "0.0000",
                "gw_base_price" => "0.0000",
                "gw_items_price" => "0.0000",
                "gw_items_base_price" => "0.0000",
                "gw_card_price" => "0.0000",
                "gw_card_base_price" => "0.0000",
              ],
            ],
            [
              "code" => "shipping",
              "title" => "Shipping & Handling",
              "value" => 0,
            ],
            [
              "code" => "tax",
              "title" => "Tax",
              "value" => 29.35,
              "area" => "taxes",
              "extension_attributes" => [
                "tax_grandtotal_details" => [
                        [
                          "amount" => 29.35,
                          "rates" => [
                                [
                                  "percent" => "15",
                                  "title" => "2",
                                ],
                          ],
                          "group_id" => 1,
                        ],
                ],
              ],
            ],
            [
              "code" => "grand_total",
              "title" => "Grand Total",
              "value" => 225,
              "area" => "footer",
            ],
            [
              "code" => "customerbalance",
              "title" => "Store Credit",
              "value" => -0,
            ],
            [
              "code" => "reward",
              "title" => "0 Reward points",
              "value" => -0,
            ],
        ],
        "extension_attributes" => [
          "reward_points_balance" => 0,
          "reward_currency_amount" => 0,
          "base_reward_currency_amount" => 0,
        ],
      ];

      if (is_array($response)) {
        $responseData = [
          'status' => TRUE,
          'data' => $response,
        ];
      }

      return new JsonResponse($responseData);
    }
    catch (\Exception $e) {
      $this->logger->notice('Error while trying to redeem aura points. Request Data: @request_data. Message: @message', [
        '@request_data' => json_encode($data),
        '@message' => $e->getMessage(),
      ]);
      return new JsonResponse($this->utility->getErrorResponse($e->getMessage(), $e->getCode()));
    }
  }

}
