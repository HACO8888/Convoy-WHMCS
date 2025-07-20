<?php
/**
 * ConvoyPanel WHMCS Server Module
 * 
 * This module integrates ConvoyPanel with WHMCS for automated server provisioning.
 * 
 * @author Haco
 * @version 1.0.0
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module metadata
 */
function convoy_MetaData()
{
    return array(
        'DisplayName' => 'ConvoyPanel',
        'APIVersion' => '1.1',
        'RequiresServer' => true,
        'DefaultNonSSLPort' => '80',
        'DefaultSSLPort' => '443',
        'ServiceSingleSignOnLabel' => '一鍵登入',
        'AdminSingleSignOnLabel' => 'Admin Login to ConvoyPanel',
    );
}

/**
 * Define configuration parameters
 */
function convoy_ConfigOptions()
{
    return array(
        'CPU Cores' => array(
            'Type' => 'text',
            'Size' => '10',
            'Default' => '2',
            'Description' => 'Number of CPU cores',
        ),
        'Memory (MB)' => array(
            'Type' => 'text',
            'Size' => '10',
            'Default' => '2048',
            'Description' => 'Memory in MB',
        ),
        'Disk Space (MB)' => array(
            'Type' => 'text',
            'Size' => '10',
            'Default' => '20480',
            'Description' => 'Disk space in MB',
        ),
        'Bandwidth (GB)' => array(
            'Type' => 'text',
            'Size' => '10',
            'Default' => '1000',
            'Description' => 'Monthly bandwidth in GB (null for unlimited)',
        ),
        'Snapshots' => array(
            'Type' => 'text',
            'Size' => '10',
            'Default' => '5',
            'Description' => 'Number of snapshots allowed',
        ),
        'Backups' => array(
            'Type' => 'text',
            'Size' => '10',
            'Default' => '5',
            'Description' => 'Number of backups allowed (null for unlimited)',
        ),
        'Template UUID' => array(
            'Type' => 'text',
            'Size' => '40',
            'Default' => '',
            'Description' => 'ConvoyPanel template UUID to use for provisioning',
        ),
        'Node ID' => array(
            'Type' => 'text',
            'Size' => '10',
            'Default' => '',
            'Description' => 'ConvoyPanel node ID to deploy on',
        ),
    );
}

/**
 * Create account
 */
function convoy_CreateAccount($params)
{
    try {
        // Get parameters
        $serverUrl = rtrim($params['serverhostname'], '/');
        $apiKey = $params['serverpassword'];
        $domain = $params['domain'];
        $clientsDetails = $params['clientsdetails'];
        
        // Configuration options
        $cpu = (int)$params['configoption1'];
        $memory = (int)$params['configoption2'] * 1024 * 1024; // Convert MB to bytes
        $disk = (int)$params['configoption3'] * 1024 * 1024; // Convert MB to bytes
        $bandwidth = $params['configoption4'] ? (int)$params['configoption4'] * 1024 * 1024 * 1024 : null; // Convert GB to bytes
        $snapshots = (int)$params['configoption5'];
        $backups = $params['configoption6'] ? (int)$params['configoption6'] : null;
        $templateUuid = $params['configoption7'];
        $nodeId = (int)$params['configoption8'];
        
        // Generate server name and hostname
        $serverName = 'vm-' . $params['serviceid'];
        $hostname = $domain ?: $serverName . '.example.com';
        
        // Create user first if needed
        $userId = convoy_GetOrCreateUser($serverUrl, $apiKey, $clientsDetails);
        if (!$userId) {
            return "Failed to create or retrieve user";
        }
        
        // Generate random password for the server
        $accountPassword = convoy_GeneratePassword();
        
        // Prepare server creation payload
        $payload = array(
            'node_id' => $nodeId,
            'user_id' => $userId,
            'name' => $serverName,
            'hostname' => $hostname,
            'vmid' => null, // Let ConvoyPanel auto-assign
            'limits' => array(
                'cpu' => $cpu,
                'memory' => $memory,
                'disk' => $disk,
                'snapshots' => $snapshots,
                'backups' => $backups,
                'bandwidth' => $bandwidth,
                'address_ids' => array() // Auto-assign IPs
            ),
            'account_password' => $accountPassword,
            'should_create_server' => true,
            'template_uuid' => $templateUuid,
            'start_on_completion' => true
        );
        
        // Make API call to create server
        $response = convoy_ApiCall($serverUrl, $apiKey, 'POST', '/api/application/servers', $payload);
        
        if ($response['success']) {
            $serverData = $response['data']['data'];
            
            // Store server information in custom fields or service properties
            convoy_UpdateServiceProperty($params['serviceid'], 'convoy_server_uuid', $serverData['uuid']);
            convoy_UpdateServiceProperty($params['serviceid'], 'convoy_user_id', $userId);
            convoy_UpdateServiceProperty($params['serviceid'], 'convoy_vmid', $serverData['vmid']);
            convoy_UpdateServiceProperty($params['serviceid'], 'convoy_root_password', $accountPassword);
            
            return 'success';
        } else {
            return "Error creating server: " . $response['message'];
        }
        
    } catch (Exception $e) {
        logModuleCall('convoy', 'CreateAccount', $params, $e->getMessage(), '', array($params['serverpassword']));
        return "Error: " . $e->getMessage();
    }
}

/**
 * Suspend account
 */
function convoy_SuspendAccount($params)
{
    try {
        $serverUrl = rtrim($params['serverhostname'], '/');
        $apiKey = $params['serverpassword'];
        $serverUuid = convoy_GetServiceProperty($params['serviceid'], 'convoy_server_uuid');
        
        if (!$serverUuid) {
            return "Server UUID not found";
        }
        
        $response = convoy_ApiCall($serverUrl, $apiKey, 'POST', "/api/application/servers/{$serverUuid}/settings/suspend");
        
        if ($response['success']) {
            return 'success';
        } else {
            return "Error suspending server: " . $response['message'];
        }
        
    } catch (Exception $e) {
        logModuleCall('convoy', 'SuspendAccount', $params, $e->getMessage(), '', array($params['serverpassword']));
        return "Error: " . $e->getMessage();
    }
}

/**
 * Unsuspend account
 */
function convoy_UnsuspendAccount($params)
{
    try {
        $serverUrl = rtrim($params['serverhostname'], '/');
        $apiKey = $params['serverpassword'];
        $serverUuid = convoy_GetServiceProperty($params['serviceid'], 'convoy_server_uuid');
        
        if (!$serverUuid) {
            return "Server UUID not found";
        }
        
        $response = convoy_ApiCall($serverUrl, $apiKey, 'POST', "/api/application/servers/{$serverUuid}/settings/unsuspend");
        
        if ($response['success']) {
            return 'success';
        } else {
            return "Error unsuspending server: " . $response['message'];
        }
        
    } catch (Exception $e) {
        logModuleCall('convoy', 'UnsuspendAccount', $params, $e->getMessage(), '', array($params['serverpassword']));
        return "Error: " . $e->getMessage();
    }
}

/**
 * Terminate account
 */
function convoy_TerminateAccount($params)
{
    try {
        $serverUrl = rtrim($params['serverhostname'], '/');
        $apiKey = $params['serverpassword'];
        $serverUuid = convoy_GetServiceProperty($params['serviceid'], 'convoy_server_uuid');
        
        if (!$serverUuid) {
            return "Server UUID not found";
        }
        
        $response = convoy_ApiCall($serverUrl, $apiKey, 'DELETE', "/api/application/servers/{$serverUuid}");
        
        if ($response['success']) {
            // Clean up stored properties
            convoy_DeleteServiceProperty($params['serviceid'], 'convoy_server_uuid');
            convoy_DeleteServiceProperty($params['serviceid'], 'convoy_user_id');
            convoy_DeleteServiceProperty($params['serviceid'], 'convoy_vmid');
            convoy_DeleteServiceProperty($params['serviceid'], 'convoy_root_password');
            
            return 'success';
        } else {
            return "Error terminating server: " . $response['message'];
        }
        
    } catch (Exception $e) {
        logModuleCall('convoy', 'TerminateAccount', $params, $e->getMessage(), '', array($params['serverpassword']));
        return "Error: " . $e->getMessage();
    }
}

/**
 * Test connection
 */
function convoy_TestConnection($params)
{
    try {
        $serverUrl = rtrim($params['serverhostname'], '/');
        $apiKey = $params['serverpassword'];
        
        $response = convoy_ApiCall($serverUrl, $apiKey, 'GET', '/api/application/users');
        
        if ($response['success']) {
            $successArray = array(
                'success' => true,
                'error' => '',
            );
        } else {
            $successArray = array(
                'success' => false,
                'error' => 'Connection failed: ' . $response['message'],
            );
        }
        
    } catch (Exception $e) {
        $successArray = array(
            'success' => false,
            'error' => $e->getMessage(),
        );
    }
    
    return $successArray;
}

/**
 * Client area output
 */
function convoy_ClientArea($params)
{
    $serverUuid = convoy_GetServiceProperty($params['serviceid'], 'convoy_server_uuid');
    $vmid = convoy_GetServiceProperty($params['serviceid'], 'convoy_vmid');
    
    if (!$serverUuid) {
        return array(
            'templatefile' => 'clientarea',
            'vars' => array(
                'error' => 'Server not found or not provisioned yet.',
            ),
        );
    }
    
    try {
        $serverUrl = rtrim($params['serverhostname'], '/');
        $apiKey = $params['serverpassword'];
        
        // Get server details
        $response = convoy_ApiCall($serverUrl, $apiKey, 'GET', "/api/application/servers/{$serverUuid}");
        
        if ($response['success']) {
            $server = $response['data']['data'];
            
            return array(
                'templatefile' => 'clientarea',
                'vars' => array(
                    'server' => $server,
                    'serverUrl' => $serverUrl,
                    'vmid' => $vmid,
                    'status' => $server['status'],
                    'hostname' => $server['hostname'],
                    'cpu' => $server['limits']['cpu'],
                    'memory' => round($server['limits']['memory'] / 1024 / 1024),
                    'disk' => round($server['limits']['disk'] / 1024 / 1024),
                    'ipAddresses' => $server['limits']['addresses']['ipv4'],
                ),
            );
        } else {
            return array(
                'templatefile' => 'clientarea',
                'vars' => array(
                    'error' => 'Failed to retrieve server information: ' . $response['message'],
                ),
            );
        }
        
    } catch (Exception $e) {
        logModuleCall('convoy', 'ClientArea', $params, $e->getMessage(), '', array($params['serverpassword']));
        return array(
            'templatefile' => 'clientarea',
            'vars' => array(
                'error' => 'Error: ' . $e->getMessage(),
            ),
        );
    }
}

/**
 * Admin area output
 */
function convoy_AdminLink($params)
{
    $serverUuid = convoy_GetServiceProperty($params['serviceid'], 'convoy_server_uuid');
    
    if ($serverUuid) {
        $serverUrl = rtrim($params['serverhostname'], '/');
        return '<a href="' . $serverUrl . '/admin/servers/' . $serverUuid . '" target="_blank">Manage Server</a>';
    }
    
    return 'Server not provisioned';
}

/**
 * Client area single sign-on
 */
function convoy_ServiceSingleSignOn($params)
{
    $serverUrl = rtrim($params['serverhostname'], '/');
    $serverUuid = convoy_GetServiceProperty($params['serviceid'], 'convoy_server_uuid');
    
    if ($serverUuid) {
        return array(
            'success' => true,
            'redirectTo' => $serverUrl . '/server/' . $serverUuid,
        );
    }
    
    return array(
        'success' => false,
        'errorMsg' => 'Server not found',
    );
}

/**
 * Admin single sign-on
 */
function convoy_AdminSingleSignOn($params)
{
    $serverUrl = rtrim($params['serverhostname'], '/');
    $serverUuid = convoy_GetServiceProperty($params['serviceid'], 'convoy_server_uuid');
    
    if ($serverUuid) {
        return array(
            'success' => true,
            'redirectTo' => $serverUrl . '/admin/servers/' . $serverUuid,
        );
    }
    
    return array(
        'success' => false,
        'errorMsg' => 'Server not found',
    );
}

// Helper functions

/**
 * Make API call to ConvoyPanel
 */
function convoy_ApiCall($serverUrl, $apiKey, $method, $endpoint, $data = null)
{
    $url = $serverUrl . $endpoint;
    
    $headers = array(
        'Authorization: Bearer ' . $apiKey,
        'Accept: application/json',
        'Content-Type: application/json',
    );
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Consider enabling in production
    
    switch (strtoupper($method)) {
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
            break;
        case 'PUT':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return array(
            'success' => false,
            'message' => 'cURL Error: ' . $error,
        );
    }
    
    $decodedResponse = json_decode($response, true);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return array(
            'success' => true,
            'data' => $decodedResponse,
        );
    } else {
        $errorMessage = 'HTTP ' . $httpCode;
        if (isset($decodedResponse['errors'])) {
            $errorMessage .= ': ' . implode(', ', array_values($decodedResponse['errors']));
        } elseif (isset($decodedResponse['message'])) {
            $errorMessage .= ': ' . $decodedResponse['message'];
        }
        
        return array(
            'success' => false,
            'message' => $errorMessage,
        );
    }
}

/**
 * Get or create user in ConvoyPanel
 */
function convoy_GetOrCreateUser($serverUrl, $apiKey, $clientDetails)
{
    // First, try to find existing user
    $response = convoy_ApiCall($serverUrl, $apiKey, 'GET', '/api/application/users?filter[email]=' . urlencode($clientDetails['email']));
    
    if ($response['success'] && !empty($response['data']['data'])) {
        return $response['data']['data'][0]['id'];
    }
    
    // User doesn't exist, create new one
    $userData = array(
        'name' => $clientDetails['firstname'] . ' ' . $clientDetails['lastname'],
        'email' => $clientDetails['email'],
        'password' => convoy_GeneratePassword(),
        'root_admin' => false,
    );
    
    $response = convoy_ApiCall($serverUrl, $apiKey, 'POST', '/api/application/users', $userData);
    
    if ($response['success']) {
        return $response['data']['data']['id'];
    }
    
    return false;
}

/**
 * Generate random password
 */
function convoy_GeneratePassword($length = 12)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*';
    $password = '';
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    
    return $password;
}

/**
 * Store service property
 */
function convoy_UpdateServiceProperty($serviceId, $key, $value)
{
    Capsule::table('tblhosting')->where('id', $serviceId)->update([
        $key => $value
    ]);
}

/**
 * Get service property
 */
function convoy_GetServiceProperty($serviceId, $key)
{
    $result = Capsule::table('tblhosting')->where('id', $serviceId)->first();
    return $result ? $result->$key : null;
}

/**
 * Delete service property
 */
function convoy_DeleteServiceProperty($serviceId, $key)
{
    Capsule::table('tblhosting')->where('id', $serviceId)->update([
        $key => null
    ]);
}