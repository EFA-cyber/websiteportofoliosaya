<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Load database configuration from environment variables
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'portfolio_db';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';

// Create database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    echo json_encode(['error' => 'Database connection failed. Please try again later.']);
    exit;
}

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Get testimonials with pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 6;
        $offset = ($page - 1) * $perPage;

        try {
            // Get total count
            $countStmt = $pdo->query("SELECT COUNT(*) FROM testimonials");
            $total = $countStmt->fetchColumn();
            $totalPages = ceil($total / $perPage);

            // Get testimonials for current page
            $stmt = $pdo->prepare("SELECT * FROM testimonials ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $testimonials = $stmt->fetchAll();

            echo json_encode([
                'testimonials' => $testimonials,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_items' => $total
                ]
            ]);
        } catch(PDOException $e) {
            error_log('Failed to fetch testimonials: ' . $e->getMessage());
            echo json_encode(['error' => 'Failed to fetch testimonials. Please try again later.']);
        }
        break;

    case 'POST':
        // Add new testimonial
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['clientName']) || !isset($data['text'])) {
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        // Sanitize input
        $clientName = htmlspecialchars(strip_tags($data['clientName']));
        $text = htmlspecialchars(strip_tags($data['text']));

        try {
            $stmt = $pdo->prepare("INSERT INTO testimonials (client_name, text) VALUES (:clientName, :text)");
            $stmt->execute([
                ':clientName' => $clientName,
                ':text' => $text
            ]);
            
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        } catch(PDOException $e) {
            error_log('Failed to add testimonial: ' . $e->getMessage());
            echo json_encode(['error' => 'Failed to add testimonial. Please try again later.']);
        }
        break;

    case 'DELETE':
        // Delete testimonial
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if (!$id) {
            echo json_encode(['error' => 'Missing testimonial ID']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            echo json_encode(['success' => true]);
        } catch(PDOException $e) {
            error_log('Failed to delete testimonial: ' . $e->getMessage());
            echo json_encode(['error' => 'Failed to delete testimonial. Please try again later.']);
        }
        break;

    default:
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 