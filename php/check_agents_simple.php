<?php
require_once '../config/database.php';

echo "=== CHECKING AGENTS IN DATABASE ===\n";

$stmt = $pdo->prepare("SELECT u.full_name, a.city, a.status FROM agents a JOIN users u ON a.user_id = u.id");
$stmt->execute();
$agents = $stmt->fetchAll();

echo "Total agents found: " . count($agents) . "\n\n";

foreach($agents as $agent) {
    echo "- " . $agent['full_name'] . " (" . $agent['city'] . ", " . $agent['status'] . ")\n";
}

echo "\n=== TESTING get_agents.php DIRECTLY ===\n";

// Include the get_agents.php logic directly
$stmt = $pdo->prepare("
    SELECT 
        u.id,
        u.full_name,
        u.email,
        a.license_number,
        a.experience,
        a.specialization,
        a.city,
        a.agency,
        a.phone,
        a.status,
        a.commission_rate,
        a.total_sales,
        a.rating,
        a.created_at
    FROM agents a
    JOIN users u ON a.user_id = u.id
    WHERE a.status IN ('approved', 'pending')
    ORDER BY a.status DESC, a.created_at DESC
");

$stmt->execute();
$agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Agents for portfolio managers: " . count($agents) . "\n";
foreach ($agents as $agent) {
    echo "- {$agent['full_name']} ({$agent['specialization']}, {$agent['city']}) - Status: {$agent['status']}\n";
}
?>
