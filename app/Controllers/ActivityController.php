<?php

class ActivityController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $user = Auth::user();
        if ($user['role'] !== 'admin') {
            Auth::flash('error', 'Access denied.');
            $this->redirect('/dashboard');
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $pdo = DB::get();
        
        // Count total
        $stmt = $pdo->query("SELECT COUNT(*) FROM activity_logs");
        $total = (int)$stmt->fetchColumn();
        $totalPages = (int)ceil($total / $perPage);

        // Fetch logs
        $stmt = $pdo->prepare("
            SELECT l.*, u.username 
            FROM activity_logs l
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.id DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $logs = $stmt->fetchAll();

        $this->view('activity/index.php', [
            'title' => 'Activity Log | Shiori',
            'logs' => $logs,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }
}
