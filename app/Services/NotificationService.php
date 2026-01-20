<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\Notification;
use Firebase\JWT\JWT;

class NotificationService
{
    /**
     * Generate Firebase access token using service account credentials
     *
     * @return string|null
     */
    private function generateAccessToken()
    {
        try {
            $privateKey = firebase_private_key();
            $clientEmail = firebase_client_email();
            
            if (empty($privateKey) || empty($clientEmail)) {
                Log::error('Firebase credentials missing');
                return null;
            }

            // Fix the private key format (replace escaped newlines with actual newlines)
            $privateKey = str_replace('\\n', "\n", $privateKey);

            $now = time();
            $payload = [
                'iss' => $clientEmail,
                'sub' => $clientEmail,
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging'
            ];

            $jwt = JWT::encode($payload, $privateKey, 'RS256');

            // Exchange JWT for access token
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['access_token'] ?? null;
            }

            Log::error('Failed to get Firebase access token', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Firebase token generation error', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Send a push notification via Firebase Cloud Messaging (HTTP v1 API)
     *
     * @param string $deviceToken
     * @param array $payload
     * @return array
     */
    public function sendPushNotification($deviceToken, $payload)
    {
        // Check if Firebase is configured
        if (!is_firebase_configured()) {
            return [
                'success' => false,
                'message' => 'Firebase is not configured properly'
            ];
        }

        try {
            $accessToken = $this->generateAccessToken();
            
            if (!$accessToken) {
                Log::warning('Could not generate Firebase access token, falling back to mock response');
                return [
                    'success' => false,
                    'message' => 'Could not generate Firebase access token'
                ];
            }

            $projectId = firebase_project_id();
            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            // Build the FCM message
            $message = [
                'message' => [
                    'token' => $deviceToken,
                    'notification' => [
                        'title' => $payload['title'] ?? 'Notification',
                        'body' => $payload['body'] ?? $payload['message'] ?? '',
                    ],
                    'data' => array_map('strval', $payload['data'] ?? []),
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            'channel_id' => 'high_importance_channel'
                        ]
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1
                            ]
                        ]
                    ]
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $message);

            if ($response->successful()) {
                Log::info('Firebase notification sent successfully', [
                    'device_token' => substr($deviceToken, 0, 20) . '...',
                    'response' => $response->json()
                ]);

                return [
                    'success' => true,
                    'message' => 'Notification sent successfully',
                    'response' => $response->json()
                ];
            }

            Log::error('Firebase notification failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'device_token' => substr($deviceToken, 0, 20) . '...'
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send notification: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Firebase notification error', [
                'error' => $e->getMessage(),
                'device_token' => substr($deviceToken, 0, 20) . '...'
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send notification to a single user
     *
     * @param User $user
     * @param array $payload
     * @param bool $saveToDatabase
     * @return array
     */
    public function sendToUser($user, $payload, $saveToDatabase = true)
    {
        // Save notification to database
        if ($saveToDatabase) {
            $this->saveNotification($user->id, $payload);
        }

        // Check if user has a device token
        if (empty($user->device_token)) {
            return [
                'success' => false,
                'message' => 'User does not have a device token',
                'saved_to_db' => $saveToDatabase
            ];
        }
        
        $result = $this->sendPushNotification($user->device_token, $payload);
        $result['saved_to_db'] = $saveToDatabase;
        
        return $result;
    }

    /**
     * Send notification to all admin users
     *
     * @param array $payload
     * @return array
     */
    public function sendToAdmins($payload)
    {
        $results = [];
        $successCount = 0;
        $failCount = 0;

        // Get all admin users
        $admins = User::whereIn('user_role', ['super_admin', 'admin'])->get();

        foreach ($admins as $admin) {
            $result = $this->sendToUser($admin, $payload);
            $result['user_id'] = $admin->id;
            $results[] = $result;

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        return [
            'success' => $successCount > 0,
            'message' => "Notifications sent: {$successCount} successful, {$failCount} failed",
            'results' => $results,
            'summary' => [
                'total_admins' => $admins->count(),
                'successful' => $successCount,
                'failed' => $failCount
            ]
        ];
    }

    /**
     * Send notification to all users in a group
     *
     * @param UserGroup $userGroup
     * @param array $payload
     * @return array
     */
    public function sendToUserGroup($userGroup, $payload)
    {
        $results = [];
        $successCount = 0;
        $failCount = 0;
        
        // Get all users in the group
        $users = $userGroup->users;
        
        foreach ($users as $user) {
            $result = $this->sendToUser($user, $payload);
            $result['user_id'] = $user->id;
            $results[] = $result;
            
            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }
        
        return [
            'success' => $successCount > 0,
            'message' => "Notifications sent: {$successCount} successful, {$failCount} failed",
            'results' => $results,
            'summary' => [
                'total_users' => $users->count(),
                'successful' => $successCount,
                'failed' => $failCount
            ]
        ];
    }

    /**
     * Send low stock alert notification
     *
     * @param \App\Models\Product $product
     * @return array
     */
    public function sendLowStockAlert($product)
    {
        $payload = [
            'title' => '⚠️ Low Stock Alert',
            'body' => "Product \"{$product->name}\" has low stock! Current quantity: {$product->stock_quantity}, Threshold: {$product->low_quantity_threshold}",
            'message' => "Product \"{$product->name}\" has low stock! Current quantity: {$product->stock_quantity}, Threshold: {$product->low_quantity_threshold}",
            'type' => 'low_stock_alert',
            'data' => [
                'type' => 'low_stock_alert',
                'product_id' => (string) $product->id,
                'product_name' => $product->name,
                'current_quantity' => (string) $product->stock_quantity,
                'threshold' => (string) $product->low_quantity_threshold
            ]
        ];

        return $this->sendToAdmins($payload);
    }

    /**
     * Save notification to database
     *
     * @param int $userId
     * @param array $payload
     * @return Notification
     */
    private function saveNotification($userId, $payload)
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $payload['title'] ?? 'Notification',
            'message' => $payload['body'] ?? $payload['message'] ?? '',
            'type' => $payload['type'] ?? 'general',
            'data' => json_encode($payload['data'] ?? []),
            'read' => false
        ]);
    }

    /**
     * Test Firebase configuration
     *
     * @return array
     */
    public function testConfiguration()
    {
        $configured = is_firebase_configured();
        
        if ($configured) {
            // Try to generate access token to verify credentials
            $accessToken = $this->generateAccessToken();
            
            if ($accessToken) {
                return [
                    'success' => true,
                    'message' => 'Firebase is properly configured and ready to send notifications.',
                    'details' => [
                        'project_id' => !empty(firebase_project_id()),
                        'client_email' => !empty(firebase_client_email()),
                        'private_key' => !empty(firebase_private_key()),
                        'token_generated' => true
                    ]
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Firebase credentials are set but token generation failed. Please check your private key.',
                'details' => [
                    'project_id' => !empty(firebase_project_id()),
                    'client_email' => !empty(firebase_client_email()),
                    'private_key' => !empty(firebase_private_key()),
                    'token_generated' => false
                ]
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Firebase is not properly configured. Please check your settings.',
            'details' => [
                'project_id' => !empty(firebase_project_id()),
                'client_email' => !empty(firebase_client_email()),
                'private_key' => !empty(firebase_private_key())
            ]
        ];
    }

    /**
     * Get Firebase notification statistics
     *
     * @return array
     */
    public function getStatistics()
    {
        // Get real statistics from database
        $totalNotifications = Notification::count();
        $readNotifications = Notification::where('read', true)->count();
        $unreadNotifications = Notification::where('read', false)->count();
        
        $recentActivity = Notification::with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'date' => $notification->created_at->format('M d, Y H:i'),
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'user' => $notification->user->name ?? 'Unknown',
                    'status' => $notification->read ? 'read' : 'unread'
                ];
            });

        return [
            'total_sent' => $totalNotifications,
            'total_read' => $readNotifications,
            'total_unread' => $unreadNotifications,
            'recent_activity' => $recentActivity
        ];
    }
}