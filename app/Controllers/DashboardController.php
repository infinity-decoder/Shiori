<?php
class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $user = Auth::user();

        $this->view('dashboard/index.php', [
            'title' => 'Dashboard | Shiori',
            'user'  => $user,
        ]);
    }
}
