<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class InventoryController extends Controller
{
    /**
     * Display the inventory test page
     */
    public function index()
    {
        return view('inventory.test');
    }

    /**
     * Test equipment endpoint by ID
     */
    public function testEquipmentById(Request $request)
    {
        $request->validate([
            'equipment_id' => 'required|string',
        ]);

        try {
            $client = new Client([
                'timeout' => 120,
                'connect_timeout' => 10,
                'verify' => true,
                'http_errors' => true,
                'force_ip_resolve' => 'v4',
            ]);

            $url = 'https://hay.cloud.coop/services/secured/equipment?equipmentId=' . urlencode($request->equipment_id);

            Log::info('Testing equipment endpoint by ID: ' . $url);

            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => 'Basic ' . config('services.customer_api.token'),
                    'Accept' => 'application/json',
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            return response()->json([
                'success' => true,
                'url' => $url,
                'data' => $data,
                'raw' => json_encode($data, JSON_PRETTY_PRINT)
            ]);

        } catch (RequestException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;
            $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;

            Log::error('Equipment API test error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'API Error: ' . $e->getMessage(),
                'status_code' => $statusCode,
                'response_body' => $errorBody,
            ], $statusCode ?? 500);

        } catch (\Exception $e) {
            Log::error('Equipment test error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test equipment endpoint by name
     */
    public function testEquipmentByName(Request $request)
    {
        $request->validate([
            'equipment_name' => 'required|string',
        ]);

        try {
            $client = new Client([
                'timeout' => 120,
                'connect_timeout' => 10,
                'verify' => true,
                'http_errors' => true,
                'force_ip_resolve' => 'v4',
            ]);

            $url = 'https://hay.cloud.coop/services/secured/equipment/name/' . urlencode($request->equipment_name);

            Log::info('Testing equipment endpoint by name: ' . $url);

            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => 'Basic ' . config('services.customer_api.token'),
                    'Accept' => 'application/json',
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            return response()->json([
                'success' => true,
                'url' => $url,
                'data' => $data,
                'raw' => json_encode($data, JSON_PRETTY_PRINT)
            ]);

        } catch (RequestException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;
            $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;

            Log::error('Equipment API test error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'API Error: ' . $e->getMessage(),
                'status_code' => $statusCode,
                'response_body' => $errorBody,
            ], $statusCode ?? 500);

        } catch (\Exception $e) {
            Log::error('Equipment test error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test facility equipment endpoint with search parameters
     */
    public function testFacilityEquipment(Request $request)
    {
        $request->validate([
            'search_type' => 'required|in:master,parent,name,mapping',
            'search_value' => 'required|string',
        ]);

        try {
            $client = new Client([
                'timeout' => 120,
                'connect_timeout' => 10,
                'verify' => true,
                'http_errors' => true,
                'force_ip_resolve' => 'v4',
            ]);

            // Build matrix parameters
            $paramMap = [
                'master' => 'masterEquipmentId',
                'parent' => 'parentEquipmentId',
                'name' => 'equipmentName',
                'mapping' => 'mappingId',
            ];

            $paramName = $paramMap[$request->search_type];
            $url = 'https://hay.cloud.coop/services/secured/facility-equipment;' . $paramName . '=' . urlencode($request->search_value);

            Log::info('Testing facility equipment endpoint: ' . $url);

            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => 'Basic ' . config('services.customer_api.token'),
                    'Accept' => 'application/json',
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            return response()->json([
                'success' => true,
                'url' => $url,
                'data' => $data,
                'raw' => json_encode($data, JSON_PRETTY_PRINT)
            ]);

        } catch (RequestException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;
            $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;

            Log::error('Facility equipment API test error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'API Error: ' . $e->getMessage(),
                'status_code' => $statusCode,
                'response_body' => $errorBody,
            ], $statusCode ?? 500);

        } catch (\Exception $e) {
            Log::error('Facility equipment test error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test custom endpoint URL
     */
    public function testCustomEndpoint(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        try {
            $client = new Client([
                'timeout' => 120,
                'connect_timeout' => 10,
                'verify' => true,
                'http_errors' => true,
                'force_ip_resolve' => 'v4',
            ]);

            // Ensure endpoint starts with /
            $endpoint = $request->endpoint;
            if (!str_starts_with($endpoint, '/')) {
                $endpoint = '/' . $endpoint;
            }

            $url = 'https://hay.cloud.coop/services/secured' . $endpoint;

            Log::info('Testing custom endpoint: ' . $url);

            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => 'Basic ' . config('services.customer_api.token'),
                    'Accept' => 'application/json',
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            return response()->json([
                'success' => true,
                'url' => $url,
                'data' => $data,
                'raw' => json_encode($data, JSON_PRETTY_PRINT)
            ]);

        } catch (RequestException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;
            $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;

            Log::error('Custom endpoint API test error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'API Error: ' . $e->getMessage(),
                'status_code' => $statusCode,
                'response_body' => $errorBody,
            ], $statusCode ?? 500);

        } catch (\Exception $e) {
            Log::error('Custom endpoint test error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
