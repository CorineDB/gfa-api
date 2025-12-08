<?php


namespace App\Traits\Helpers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


trait SmsTrait{

    // You should define these in a class that uses this trait, or you can use env() directly
    protected $apiUrl;
    protected $apiKey;

    public function sendSms($message, $phoneNumbers): void
    {
        // You can initialize the configuration values directly in the trait
        $this->apiUrl = config('services.sms.url'); // URL from config file
        $this->apiKey = config('services.sms.api_key'); // API Key from config file

        $endpoint = $this->apiUrl . '/sendbatch'; // Define URL in .env
        //$endpoint = $this->apiUrl . '/sms'; // Define URL in .env

        $headers = [
            'Authorization' => "Basic {$this->apiKey}",
            'Content-Type' => 'application/json',
        ];

        $phoneNumbers = is_array($phoneNumbers) ? $phoneNumbers : [$phoneNumbers]; // ✅ Ensures it's always an array
	Log::info("SMS Response: " . json_encode($phoneNumbers));
        // Prepare the data for SMS API
        $request_body = [
            "globals" => [
                "from"=>  "GFA"
            ],
            "messages" => [
                [
                    "to" => $phoneNumbers,
                    "content" => $message
                ]
            ]
        ];

        try {

            /*foreach ($phoneNumbers as $key => $phoneNumber) {

                // Prepare the data for SMS API
                $request_body = [
                    "from"=>  "GFA",
                    "to" => $phoneNumber,
                    "content" => $message
                ];
    
                // Send the request (HTTP client)
                $response = Http::withHeaders($headers)->post($endpoint, $request_body);
	    }*/
            // Send the request (HTTP client)
            $response = Http::withHeaders($headers)->post($endpoint, $request_body);

            // Log or handle the response if needed
            $responseBody = $response->json();

            // Handle the response
            if ($response->successful()) {
		Log::info("SMS Response: " . json_encode($responseBody));
            } else {
		Log::error('Failed to send SMS balance: ' . json_encode($responseBody));
                throw new \Exception("Error Processing SMS Request: " . json_encode($responseBody), 1);
            }
        } catch (\Exception $e) {
            Log::error('Error sending bulk SMS : ' . $e->getMessage());
        }
    }

    public function getSmsBalance()
    {
        // You can initialize the configuration values directly in the trait
        $this->apiUrl = config('services.sms.url'); // URL from config file
        $this->apiKey = config('services.sms.api_key'); // API Key from config file
        $endpoint = $this->apiUrl . '/account/balance'; // Define URL in .env
        
        try {
            $response = Http::withHeaders([
                'Authorization' => "Basic {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->get($endpoint);
            
            if ($response->successful()) {
                $data = $response->json()['data'];
                $balance = $data['balance'] ?? 0;

                Log::info("Current SMS balance: {$balance}");
                
            } else {
                Log::error('Failed to fetch SMS balance: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Error checking SMS balance: ' . $e->getMessage());
        }
    }
}
