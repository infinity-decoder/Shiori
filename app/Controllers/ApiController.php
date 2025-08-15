<?php
class ApiController extends Controller
{
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

            $stmt = $pdo->query("SELECT COUNT(*) FROM students");
            $totalStudents = (int)$stmt->fetchColumn();

            $stmt = $pdo->query("SELECT COUNT(*) FROM classes");
            $totalClasses = (int)$stmt->fetchColumn();

            $stmt = $pdo->query("SELECT COUNT(*) FROM sections");
            $totalSections = (int)$stmt->fetchColumn();

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

    /**
     * GET /api/search?q=...&page=1&per_page=10
     */
    public function search(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $q = trim((string)($_GET['q'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = (int)($_GET['per_page'] ?? 10);
        if ($perPage <= 0) $perPage = 10;
        $perPage = min(50, $perPage);

        if ($q === '') {
            echo json_encode(['total' => 0, 'per_page' => $perPage, 'page' => $page, 'results' => []], JSON_UNESCAPED_UNICODE);
            return;
        }

        try {
            $pdo = DB::get();
            $dbCfg = require BASE_PATH . '/config/database.php';
            // detect fulltext index presence
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'students' AND INDEX_NAME = 'ft_students_name'");
            $stmt->execute([$dbCfg['name']]);
            $hasFT = ((int)$stmt->fetchColumn() > 0);

            $isNumericLike = preg_match('/^[\d\-\s]+$/', $q) === 1; // digits, dashes, spaces only => treat as numeric-ish

            $offset = ($page - 1) * $perPage;

            if ($isNumericLike) {
                $digits = preg_replace('/\D+/', '', $q);
                $idExact = ctype_digit($digits) ? (int)$digits : 0;
                $likeParam = '%' . $q . '%';
                $digitsLike = '%' . $digits . '%';
                // Count
                $countSql = "
                    SELECT COUNT(*) FROM students s
                    WHERE (s.id = :idExact
                        OR s.roll_no LIKE :like
                        OR s.enrollment_no LIKE :like
                        OR s.cnic LIKE :digitsLike
                        OR s.mobile LIKE :like)
                ";
                $countStmt = $pdo->prepare($countSql);
                $countStmt->execute([
                    ':idExact'   => $idExact,
                    ':like'      => $likeParam,
                    ':digitsLike'=> $digitsLike,
                ]);
                $total = (int)$countStmt->fetchColumn();

                // Select with basic relevance scoring (higher value for exact id / exact fields)
                $selectSql = "
                    SELECT s.id, s.roll_no, s.enrollment_no, s.student_name, s.father_name,
                           s.cnic, s.mobile, s.photo_path, c.name AS class_name, sec.name AS section_name,
                           ((s.id = :idExact) * 100
                             + (s.roll_no = :exact) * 90
                             + (s.enrollment_no = :exact) * 90
                             + (s.cnic = :digitsExact) * 80
                             + (s.mobile = :exact) * 70
                             + (s.roll_no LIKE :likePrefix) * 50
                             + (s.enrollment_no LIKE :likePrefix) * 50
                             + (s.cnic LIKE :digitsLike) * 40
                           ) AS relevance
                    FROM students s
                    LEFT JOIN classes c ON s.class_id = c.id
                    LEFT JOIN sections sec ON s.section_id = sec.id
                    WHERE (s.id = :idExact
                        OR s.roll_no LIKE :like
                        OR s.enrollment_no LIKE :like
                        OR s.cnic LIKE :digitsLike
                        OR s.mobile LIKE :like)
                    ORDER BY relevance DESC, s.updated_at DESC
                    LIMIT :limit OFFSET :offset
                ";
                $stmt = $pdo->prepare($selectSql);
                // bind values
                $stmt->bindValue(':idExact', $idExact, PDO::PARAM_INT);
                $stmt->bindValue(':exact', $q, PDO::PARAM_STR);
                $stmt->bindValue(':digitsExact', $digits, PDO::PARAM_STR);
                $stmt->bindValue(':like', $likeParam, PDO::PARAM_STR);
                $stmt->bindValue(':digitsLike', $digitsLike, PDO::PARAM_STR);
                $stmt->bindValue(':likePrefix', $q . '%', PDO::PARAM_STR);
                $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // non-numeric query: prefer fulltext if available
                if ($hasFT) {
                    // build boolean-mode terms (append * for prefix match)
                    $terms = preg_split('/\s+/', $q);
                    $ftsParts = [];
                    foreach ($terms as $t) {
                        $t = trim($t);
                        if ($t === '') continue;
                        // make a safe token
                        $t = preg_replace('/[^\p{L}\p{N}_-]/u', '', $t);
                        if ($t === '') continue;
                        $ftsParts[] = $t . '*';
                    }
                    $fts = implode(' ', $ftsParts);
                    if ($fts === '') {
                        // fallback to LIKE if terms got removed
                        $hasFT = false;
                    } else {
                        // Count
                        $countSql = "
                            SELECT COUNT(*) FROM students s
                            WHERE MATCH(s.student_name, s.father_name) AGAINST(:fts IN BOOLEAN MODE)
                        ";
                        $countStmt = $pdo->prepare($countSql);
                        $countStmt->execute([':fts' => $fts]);
                        $total = (int)$countStmt->fetchColumn();

                        // Select with relevance
                        $selectSql = "
                            SELECT s.id, s.roll_no, s.enrollment_no, s.student_name, s.father_name,
                                   s.cnic, s.mobile, s.photo_path, c.name AS class_name, sec.name AS section_name,
                                   MATCH(s.student_name, s.father_name) AGAINST(:fts IN BOOLEAN MODE) AS score
                            FROM students s
                            LEFT JOIN classes c ON s.class_id = c.id
                            LEFT JOIN sections sec ON s.section_id = sec.id
                            WHERE MATCH(s.student_name, s.father_name) AGAINST(:fts IN BOOLEAN MODE)
                            ORDER BY score DESC, s.updated_at DESC
                            LIMIT :limit OFFSET :offset
                        ";
                        $stmt = $pdo->prepare($selectSql);
                        $stmt->bindValue(':fts', $fts, PDO::PARAM_STR);
                        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
                        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                        $stmt->execute();
                        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    }
                }

                if (!$hasFT) {
                    // LIKE fallback across names and some identifiers
                    $likeParam = '%' . $q . '%';
                    $countSql = "
                        SELECT COUNT(*) FROM students s
                        WHERE s.student_name LIKE :like OR s.father_name LIKE :like
                           OR s.roll_no LIKE :like OR s.enrollment_no LIKE :like
                           OR s.cnic LIKE :like OR s.mobile LIKE :like
                    ";
                    $countStmt = $pdo->prepare($countSql);
                    $countStmt->execute([':like' => $likeParam]);
                    $total = (int)$countStmt->fetchColumn();

                    $selectSql = "
                        SELECT s.id, s.roll_no, s.enrollment_no, s.student_name, s.father_name,
                               s.cnic, s.mobile, s.photo_path, c.name AS class_name, sec.name AS section_name,
                               ((s.student_name LIKE :prefix)*50 + (s.student_name LIKE :like)*30 + (s.father_name LIKE :prefix)*40 + (s.father_name LIKE :like)*20) AS relevance
                        FROM students s
                        LEFT JOIN classes c ON s.class_id = c.id
                        LEFT JOIN sections sec ON s.section_id = sec.id
                        WHERE s.student_name LIKE :like OR s.father_name LIKE :like
                           OR s.roll_no LIKE :like OR s.enrollment_no LIKE :like
                           OR s.cnic LIKE :like OR s.mobile LIKE :like
                        ORDER BY relevance DESC, s.updated_at DESC
                        LIMIT :limit OFFSET :offset
                    ";
                    $stmt = $pdo->prepare($selectSql);
                    $stmt->bindValue(':prefix', $q . '%', PDO::PARAM_STR);
                    $stmt->bindValue(':like', $likeParam, PDO::PARAM_STR);
                    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
                    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                    $stmt->execute();
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            }

            // Build result payload
            $appCfg = require BASE_PATH . '/config/app.php';
            $baseUrl = rtrim($appCfg['base_url'], '/');

            $results = [];
            foreach ($rows as $r) {
                $id = (int)$r['id'];
                $results[] = [
                    'id'            => $id,
                    'student_name'  => $r['student_name'] ?? '',
                    'father_name'   => $r['father_name'] ?? '',
                    'roll_no'       => $r['roll_no'] ?? '',
                    'enrollment_no' => $r['enrollment_no'] ?? '',
                    'cnic'          => $r['cnic'] ?? '',
                    'mobile'        => $r['mobile'] ?? '',
                    'class_name'    => $r['class_name'] ?? '',
                    'section_name'  => $r['section_name'] ?? '',
                    'photo_path'    => $r['photo_path'] ?? null,
                    'view_url'      => $baseUrl . '/students/show?id=' . $id,
                    'edit_url'      => $baseUrl . '/students/edit?id=' . $id,
                    'delete_url'    => $baseUrl . '/students/delete?id=' . $id,
                ];
            }

            echo json_encode([
                'total'    => $total ?? 0,
                'per_page' => $perPage,
                'page'     => $page,
                'results'  => $results,
            ], JSON_UNESCAPED_UNICODE);

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error']);
        }
    }
}
