<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UtilityController extends Controller
{

    public function errorCode($code) {
        $errorCode = array(
            "00"    =>  "Successful",
            "01"    =>  "Request Token Expired.",
            "02"    =>  "Created",
            "03"    =>  "Invalid Credentials",
            "04"    =>  "User not allowed. Contact Administrator for support",
            "05"    =>  "Validation Error",
            "06"    =>  "No Result found",
            "07"    =>  "Item has been Deleted",
            "08"    =>  "Invalid amount paid",
            "09"    =>  "Transaction reference not found",
            "10"    =>  "Service Unavailable"
        );
        return $errorCode[$code];
    }

    public function headerCode($code) {
        $headerCode = array(
            "00"    =>  200,
            "01"    =>  419,
            "02"    =>  201,
            "03"    =>  401,
            "04"    =>  403,
            "05"    =>  406,
            "06"    =>  200,
            "07"    =>  200,
            "08"    =>  400,
            "09"    =>  404,
            "10"    =>  503
        );
        return $headerCode[$code];
    }

    public function response($status, $dataName, $data) {
        $errorMessage = $this->errorCode($status); // get error code message
        $header_code = $this->headerCode($status); // get header code message
        $resp = array(
            'status'    =>  $status,
            'message'   =>  $errorMessage,
        );
        if(count($data) > 0) {
            $resp[$dataName] = $data;
        }
        return response()->json($resp, $header_code);
    }

    public function verifyPayment($paymentReference, $amount, $mode = 1) {
        \Log::info('lets try to verify payment on paystack');
        $verified = 0;
        $result = array();
        $key = env('PAYSTACK_TEST_PRIVATE_KEY');
        if($mode == 2) {
            $key = env('PAYSTACK_LIVE_PRIVATE_KEY');
        }
        $url = 'https://api.paystack.co/transaction/verify/' . $paymentReference;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $key]
        );
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $request = curl_exec($ch);

        if(curl_errno($ch)) {
            $verified = curl_errno($ch);
            \Log::info('cURL error occured while trying to verify payment.');
            Log::error(curl_error($ch));
        } else {
            if ($request) {
                $result = json_decode($request, true);
                \Log::info('result from verifying payment');
                \Log::info($result);
                if($result["status"] == true) {
                    if($result["data"] && $result["data"]["status"] == "success") {
                        // at this point, payment has been verified.
                        // launch an event on a queue to send email of receipt to user.
                        \Log::info('Payment successfully verified.');
                        $real_amount_paid = $result['data']['amount'] / 100;
                        if($amount == $real_amount_paid) {
                            $verified = 100;
                        } else {
                            $verified = 419;
                        }
                    } else {
                        $verified = 404;
                    }
                }  else {
                    // $resp['msg'] = 'Transaction not found!';
                    $verified = 404;
                }
            } else {
                // $resp['msg'] = 'Unable to verify transaction!';
                $verified = 503;
            }
        }
        curl_close($ch);

        return $verified;
    }

}
