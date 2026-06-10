<?php

declare(strict_types=1);

class ProfileController extends Controller
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('profile/index', [
            'title' => 'Profile',
            'user' => $this->users->find(current_user_id()),
        ]);
    }

    public function changePassword(): void
    {
        $this->requireAuth();
        verify_csrf();

        $current = (string)($_POST['current_password'] ?? '');
        $new = (string)($_POST['new_password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');
        $email = '';
        $profile = $this->users->find(current_user_id());

        if ($profile) {
            $email = $profile['email'];
        }

        $fullUser = $this->users->findByEmail($email);
        if (!$fullUser || !password_verify($current, $fullUser['password'])) {
            flash('error', 'Current password is incorrect.');
            redirect('profile');
        }

        if (strlen($new) < 6) {
            flash('error', 'New password must be at least 6 characters.');
            redirect('profile');
        }

        if ($new !== $confirm) {
            flash('error', 'New password confirmation does not match.');
            redirect('profile');
        }

        $this->users->updatePassword(current_user_id(), $new);
        flash('success', 'Password changed successfully.');
        redirect('profile');
    }
}

