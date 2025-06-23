<?php
/**
 * Populate sample data from schema_complete.sql
 * Script để nhập dữ liệu mẫu vào database
 */

require_once '../includes/config.php';
require_once '../classes/Database.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
    
    // Read and execute schema_complete.sql
    $schemaFile = '../sql/schema_complete.sql';
    
    if (!file_exists($schemaFile)) {
        throw new Exception('Schema file not found: ' . $schemaFile);
    }
    
    $sql = file_get_contents($schemaFile);
    
    // Split SQL statements by semicolon
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $executed = 0;
    $errors = [];
    
    foreach ($statements as $statement) {
        if (empty($statement) || substr($statement, 0, 2) === '--') {
            continue;
        }
        
        try {
            $db->execute($statement);
            $executed++;
        } catch (Exception $e) {
            $errors[] = [
                'statement' => substr($statement, 0, 100) . '...',
                'error' => $e->getMessage()
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database populated successfully',
        'executed_statements' => $executed,
        'errors' => $errors
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
