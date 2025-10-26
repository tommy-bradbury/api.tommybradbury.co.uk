<?php
declare(strict_types=1);

/**
 * Get PDO connection to db
 * 
 * @return PDO
 * @throws \PDOException
 */
function getPDOConnection(): PDO {
    $dsn = 'mysql:host=db;dbname=' . $_ENV['DB_NAME'];
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }

    return $pdo;
}

/**
 * Signs up a new user and stores their password.
 *
 * @param string $username
 * @param string $password
 * @param string $email
 * @param PDO $pdo
 * @return bool|string Returns true on success, or an error message string on failure.
 */
function sign_up(string $username, string $password, string $email, PDO $pdo): bool|string {
    if(empty($username) || empty($password) || empty($email)) {
        return "All fields are required.";
    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format.";
    }

    if(strlen($password) < 8) {
        return "Password must be at least 8 characters long.";
    }
    if(!preg_match('/[A-Z]/', $password)) {
        return "Password must contain at least one uppercase letter.";
    }
    if(!preg_match('/[a-z]/', $password)) {
        return "Password must contain at least one lowercase letter.";
    }
    if(!preg_match('/[0-9]/', $password)) {
        return "Password must contain at least one number.";
    }
    if(!preg_match('/[^A-Za-z0-9]/', $password)) {
        return "Password must contain at least one special character.";
    }

    // prevent XSS
    $username   = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $email      = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $hashed     = password_hash($password, PASSWORD_DEFAULT);

    if($hashed === false) {
        return "Password hashing failed.";
    }

    try {
        $sql = "SELECT id FROM `users` WHERE `username` = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        if($stmt->fetch(PDO::FETCH_ASSOC)) {
            return "Username already exists.";
        }
    } catch(PDOException $e) {
        error_log("Sign-up database error: " . $e->getMessage());
        return "An error occurred during registration. Please try again.";
    }
    try {
        $sql = "SELECT id FROM `users` WHERE `email` = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        if($stmt->fetch(PDO::FETCH_ASSOC)) {
            return "Username already exists.";
        }
    } catch(PDOException $e) {
        error_log("Sign-up database error: " . $e->getMessage());
        return "An error occurred during registration. Please try again.";
    }

    try {
        $sql = "INSERT INTO `users` (`username`, `email`, `password`) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $email, $hashed]);
        return true;
    } catch(PDOException $e) {
        error_log("Sign-up database error: " . $e->getMessage());
        return "An error occurred during registration. {$e->getMessage()} Please try again.";
    }
}

/**
 * Authenticates a user and establishes a secure session.
 *
 * @param string $username
 * @param string $password
 * @param PDO $pdo
 * @return bool|string Returns true on successful login, or an error message string on failure.
 */
function log_in(string $username, string $password, PDO $pdo): bool|string {
    if (empty($username) || empty($password)) {
        return "Username and password are required.";
    }

    try {
        $sql    = "SELECT `id`, `password` FROM `users` WHERE `username` = ?";
        $stmt   = $pdo->prepare($sql);
        $stmt->execute([$username]);
        $user   = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user) {
            if(password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']        = $user['id'];
                $_SESSION['is_logged_in']   = true;

                return true;
            }
        }
    } catch (PDOException $e) {
        error_log("Login database error: " . $e->getMessage());
        return "An authentication error occurred. Please try again.";
    }

    return "Invalid username or password.";
}

/**
 * Logs out the current user by ending and destroying their session.
 *
 * @return void
 */
function log_out(): void {
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}

/**
 * Check if a user is currently logged in.
 *
 * @return bool
 */
function is_logged_in(): bool {
    return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
}

/**
 * Get the ID of the currently logged-in user.
 *
 * @return int|null Returns the user ID or null if the user is not logged in.
 */
function get_user_id(): ?int {
    return is_logged_in() ? (int)$_SESSION['user_id'] : null;
}

/**
 * Get all user data from the database for the current session.
 *
 * @param PDO
 * @return array|null 
 */
function get_user_data(PDO $pdo): ?array {
    $user_id = get_user_id();

    if (!$user_id) {
        return null;
    }

    try {
        $sql = "SELECT id, username, email FROM users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        error_log("Get user data database error: " . $e->getMessage());
        return null;
    }
}
