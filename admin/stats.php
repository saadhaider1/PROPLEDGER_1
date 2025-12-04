<?php
// admin/stats.php - Simplified for development
// CORS headers
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (preg_match('/^http:\/\/localhost(:\d+)?$/', $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: http://localhost:3000');
}
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/database.php';

// For development: Allow all requests from localhost
// TODO: In production, implement proper authentication
session_start();

try {
    // Count Users
    $stmtUser = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmtUser->fetch()['count'];

    // Count Agents
    $stmtAgent = $pdo->query("SELECT COUNT(*) as count FROM agents");
    $agentCount = $stmtAgent->fetch()['count'];

    // Count Properties (all types)
    $stmtAllProperties = $pdo->query("SELECT COUNT(*) as count FROM properties");
    $allPropertiesCount = $stmtAllProperties->fetch()['count'];

    // Count Standard Properties
    $stmtStandard = $pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE property_type = ?");
    $stmtStandard->execute(['standard']);
    $standardCount = $stmtStandard->fetch()['count'];

    // Count Crowdfunding Campaigns
    $stmtCrowdfunding = $pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE property_type = ?");
    $stmtCrowdfunding->execute(['crowdfunding']);
    $crowdfundingCount = $stmtCrowdfunding->fetch()['count'];

    // Count Investment Properties
    $stmtInvestment = $pdo->prepare("SELECT COUNT(*) as count FROM properties WHERE property_type = ?");
    $stmtInvestment->execute(['investment']);
    $investmentCount = $stmtInvestment->fetch()['count'];

    echo json_encode([
        'success' => true,
        'stats' => [
            'total_users' => (int)$userCount,
            'total_agents' => (int)$agentCount,
            'total_properties' => (int)$allPropertiesCount,
            'standard_properties' => (int)$standardCount,
            'crowdfunding_campaigns' => (int)$crowdfundingCount,
            'investment_opportunities' => (int)$investmentCount
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
