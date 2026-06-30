<?php
session_start();


if (!isset($_SESSION['user_name'])) {
    header('Location: contacts.html');
    exit;
}

$current_user_name = $_SESSION['user_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim(htmlentities($_POST['username'] ?? ''));
    $new_password = $_POST['new_password'] ?? '';
    
    try {
        $db = new PDO('sqlite:database.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        
        $avatar_path = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            
            $file_tmp = $_FILES['avatar']['tmp_name'];
            $file_name = time() . '_' . basename($_FILES['avatar']['name']); 
            $target_file = 'uploads/' . $file_name;
            
            
            if (move_uploaded_file($file_tmp, $target_file)) {
                $avatar_path = $target_file;
            }
        }
        
        
        if ($avatar_path) {
            $stmt = $db->prepare("UPDATE users SET avatar = :avatar WHERE full_name = :current_name");
            $stmt->execute([':avatar' => $avatar_path, ':current_name' => $current_user_name]);
        }
        
        
        if (!empty($new_username) && $new_username !== $current_user_name) {
            $stmt = $db->prepare("UPDATE users SET full_name = :new_name WHERE full_name = :current_name");
            $stmt->execute([':new_name' => $new_username, ':current_name' => $current_user_name]);
            $_SESSION['user_name'] = $new_username; 
            $current_user_name = $new_username;
        }
        
        
        if (!empty($new_password)) {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = :pass WHERE full_name = :current_name");
            $stmt->execute([':pass' => $password_hash, ':current_name' => $current_user_name]);
        }
        
        
        ?>
        <!DOCTYPE html>
        <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Профиль обновлен | EcoKazakhstan</title>
            <link rel="stylesheet" href="style.css">
            <meta http-equiv="refresh" content="3;url=profile.php">
        </head>
        <body>
            <header class="main-header">
                <div class="container header-flex">
                    <a href="index.html" class="logo">Eco<span>Kazakhstan</span></a>
                </div>
            </header>

            <main style="background-color: #f8faf9; padding: 100px 0; min-height: 60vh; display: flex; justify-content: center; align-items: center;">
                <div class="container" style="display: flex; justify-content: center;">
                    
                    <div class="eco-welcome-card" style="background: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 480px; width: 100%; text-align: center; border-top: 4px solid #1b3a24;">
                        
                        <div style="width: 80px; height: 80px; background: #e8f5e9; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 20px auto; border: 2px solid #2ecc71;">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 16.17L4.83 12L3.41 13.41L9 19L21 7L19.59 5.59L9 16.17Z" fill="#1b3a24"/>
                            </svg>
                        </div>

                        <h2 style="color: #1b3a24; font-size: 1.8rem; margin-bottom: 10px; font-weight: 700;">Профиль обновлен!</h2>
                        <p style="color: #555555; font-size: 1.1rem; margin-bottom: 20px;">Ваши новые настройки и фотография волонтера успешно сохранены.</p>
                        
                        <p style="color: #777777; font-size: 0.9rem; margin-bottom: 25px;">Возвращаемся в личный кабинет через несколько секунд...</p>
                        
                        <a href="profile.php" style="display: block; text-decoration: none; text-align: center; background-color: #1b3a24; color: #ffffff; padding: 12px; border-radius: 6px; font-weight: bold;">
                            Вернуться в кабинет
                        </a>
                    </div>

                </div>
            </main>

            <footer class="main-footer">
                <div class="container footer-flex">
                    <p>&copy; 2026 EcoKazakhstan. Экологический проект.</p>
                </div>
            </footer>
        </body>
        </html>
        <?php
        exit;

    } catch (PDOException $e) {
        die("Ошибка при обновлении профиля: " . $e->getMessage());
    }
} else {
    header('Location: profile.php');
    exit;
}