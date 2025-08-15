<?php
class ApiController extends Controller
{
    /**
     * GET /api/stats
     * Returns JSON:
     * {
     *   "total_students": int,
     *   "total_classes": int,
     *   "total_sections": int,
     *   "categories": [
     *     {"name":"civil","count":12}, ...
     *   ]
     * }
     */
    public function stats(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        try {
            $pdo = DB::get();

            // Total students
            $stmt = $pdo->query("SELECT COUNT(*) FROM students");
            $totalStudents = (int)$stmt->fetchColumn();

            // Total classes
            $stmt = $pdo->query("SELECT COUNT(*) FROM classes");
            $totalClasses = (int)$stmt->fetchColumn();

            // Total sections
            $stmt = $pdo->query("SELECT COUNT(*) FROM sections");
            $totalSections = (int)$stmt->fetchColumn();

            // Category distribution (left join to include categories with zero students)
            $stmt = $pdo->query("
                SELECT c.name AS name, COUNT(s.id) AS cnt
                FROM categories c
                LEFT JOIN students s ON s.category_id = c.id
                GROUP BY c.id, c.name
                ORDER BY c.id
            ");
            $cats = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cats[] = [
                    'name'  => $row['name'],
                    'count' => (int)$row['cnt'],
                ];
            }

            $payload = [
                'total_students' => $totalStudents,
                'total_classes'  => $totalClasses,
                'total_sections' => $totalSections,
                'categories'      => $cats,
            ];

            echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error']);
        }
    }
}
