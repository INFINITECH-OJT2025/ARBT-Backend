<?php

namespace App\Services;

use Google\Client;  // Import Google_Client class from the Google namespace
use Illuminate\Support\Facades\Http;

class FirebaseMessagingService
{
    protected $client;
    protected $projectId;

    public function __construct()
    {
        $this->projectId = env('FIREBASE_PROJECT_ID'); // Get Firebase Project ID from the .env file
        $this->client = new Client();  // Create an instance of the Google_Client

        // Load the credentials from the service account JSON file
        $this->client->setAuthConfig(storage_path('app/firebase/firebase-credentials.json'));
        $this->client->addScope('https://www.googleapis.com/auth/firebase.messaging'); // Add the necessary scope

        // Fetch the access token using service account credentials
        $accessToken = $this->client->fetchAccessTokenWithAssertion()['access_token'];
        $this->client->setAccessToken($accessToken);  // Set the access token in the client
    }

    public function sendNotificationToToken(string $token, string $title, string $body)
    {
        // Retrieve the access token
        $accessToken = $this->client->getAccessToken()['access_token'];

        // Send the push notification using Firebase Cloud Messaging HTTP v1 API
        $response = Http::withToken($accessToken)->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", [
            'message' => [
                'token' => $token,  // The FCM token of the target device
                'notification' => [
                    'title' => $title,  // Title of the notification
                    'body' => $body,    // Body text of the notification
                ],
                'android' => [
                    'priority' => 'high',  // Set priority for Android
                ],
                'webpush' => [
                    'headers' => [
                        'Urgency' => 'high',  // Set urgency for web push notifications
                    ],
                ],
            ],
        ]);

        // Return the response from Firebase
        return $response->json();
    }
}