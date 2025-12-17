<?php

class DeveloperController extends Controller
{
    public function index(): void
    {
        $this->view('developer/index.php', [
            'title' => 'Developer Info | INFINITY DECODER',
        ]);
    }
}
