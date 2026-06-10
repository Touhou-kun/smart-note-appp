<?php

declare(strict_types=1);

class AuthController extends Controller
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function register(): void
    {
        if (is_logged_in()) {
            redirect('dashboard');
        }
        $this->view('auth/register', [], 'auth');
    }

    public function store(): void
    {
        verify_csrf();
        $username = trim($_POST['username'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        if ($username === '' || $email === '' || $password === '' || $confirm === '') {
            flash('error', 'All fields are required.');
            redirect('register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid email address.');
            redirect('register');
        }

        if (strlen($password) < 6) {
            flash('error', 'Password must be at least 6 characters.');
            redirect('register');
        }

        if ($password !== $confirm) {
            flash('error', 'Password confirmation does not match.');
            redirect('register');
        }

        if ($this->users->findByEmail($email)) {
            flash('error', 'Email is already registered.');
            redirect('register');
        }

        $this->users->create($username, $email, $password);
        flash('success', 'Account created. You can now log in.');
        redirect('login');
    }

    public function login(): void
    {
        if (is_logged_in()) {
            redirect('dashboard');
        }
        $this->view('auth/login', [], 'auth');
    }

    public function authenticate(): void
    {
        verify_csrf();
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            flash('error', 'Email and password are required.');
            redirect('login');
        }

        $user = $this->users->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            flash('error', 'Invalid email or password.');
            redirect('login');
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        flash('success', 'Welcome back, ' . $user['username'] . '.');
        redirect('dashboard');
    }

    public function logout(): void
    {
        verify_csrf();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();
        redirect('login');
    }
}

