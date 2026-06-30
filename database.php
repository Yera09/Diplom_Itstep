<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'register'; 
    $email = trim(htmlentities($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    try {
        $db = new PDO('sqlite:database.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT, 
            full_name TEXT NOT NULL,
            email TEXT NOT NULL,
            phone TEXT NOT NULL,
            password TEXT NOT NULL,
            avatar TEXT DEFAULT ''
        )");

        
        if ($action === 'login') {
            if (empty($email) || empty($password)) {
                echo "<script>alert('Заполните почту и пароль!'); window.location.href='contacts.html';</script>";
                exit;
            }

            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            
            if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_phone'] = $user['phone'];
                
                showWelcomePage($user['full_name']);
            } else {
                echo "<script>alert('Неверная почта или пароль!'); window.location.href='contacts.html';</script>";
                exit;
            }

        
        } else {
            $full_name = trim(htmlentities($_POST['full_name'] ?? ''));
            $phone = trim(htmlentities($_POST['phone'] ?? ''));

            if (empty($full_name) || empty($email) || empty($password)) {
                echo "<script>alert('Заполните обязательные поля!'); window.location.href='contacts.html';</script>";
                exit;
            }

            
            $check = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $check->execute([':email' => $email]);
            if ($check->fetch()) {
                echo "<script>alert('Пользователь с такой почтой уже зарегистрирован!'); window.location.href='contacts.html';</script>";
                exit;
            }

            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $db->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (:name, :email, :phone, :pass)");
            $ins->execute([
                ':name'  => $full_name,
                ':email' => $email,
                ':phone' => $phone,
                ':pass'  => $password_hash
            ]);

            $_SESSION['user_name'] = $full_name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_phone'] = $phone;

            showWelcomePage($full_name);
        }

    } catch (PDOException $e) {
        die("Ошибка базы данных: " . $e->getMessage());
    }
} else {
    header('Location: contacts.html');
    exit;
}


function showWelcomePage($name) {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Добро пожаловать | EcoKazakhstan</title>
        <link rel="stylesheet" href="style.css">
        <meta http-equiv="refresh" content="2;url=profile.php">
    </head>
    <body>
        <header class="main-header"><div class="container header-flex"><a href="index.html" class="logo">Eco<span>Kazakhstan</span></a></div></header>
        <main style="background-color: #f8faf9; padding: 80px 0; min-height: 60vh; display: flex; justify-content: center; align-items: center;">
            <div class="eco-welcome-card" style="background: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 480px; width: 100%; text-align: center; border-top: 4px solid #1b3a24;">
                <div style="width: 80px; height: 80px; background: #e8f5e9; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 20px auto; border: 2px solid #2ecc71;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 16.17L4.83 12L3.41 13.41L9 19L21 7L19.59 5.59L9 16.17Z" fill="#1b3a24"/></svg>
                </div>
                <h2 style="color: #1b3a24; font-size: 1.8rem; margin-bottom: 10px;">Рады вас видеть!</h2>
                <p style="color: #555555; font-size: 1.1rem; margin-bottom: 20px;">Добро пожаловать назад, <br><span style="color: #2ecc71; font-weight: bold;"><?php echo htmlentities($name); ?></span>!</p>
                <p style="color: #777777; font-size: 0.9rem; margin-bottom: 25px;">Выполняется вход в личный кабинет...</p>
                <a href="profile.php" style="display: block; text-decoration: none; text-align: center; background-color: #1b3a24; color: #ffffff; padding: 12px; border-radius: 6px; font-weight: bold;">Открыть профиль</a>
            </div>
        </main>
        <footer class="main-footer"><div class="container footer-flex"><p>&copy; 2026 EcoKazakhstan.</p></div></footer>
    </body>
    </html>
    <?php
    exit;
}
?>