<?php
// 1. Открываем сессию, чтобы узнать, какой пользователь вошел
session_start();

// Если пользователь не авторизован, вернем его на страницу входа
if (!isset($_SESSION['user_name'])) {
    header('Location: contacts.html');
    exit;
}

$user_name = $_SESSION['user_name'];
$avatar_path = ''; // Изначально пустой путь

try {
    // 2. Подключаемся к базе, чтобы вытащить путь к файлу аватарки
    $db = new PDO('sqlite:database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $db->prepare("SELECT avatar FROM users WHERE full_name = :name LIMIT 1");
    $stmt->execute([':name' => $user_name]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user_data && !empty($user_data['avatar'])) {
        $avatar_path = $user_data['avatar']; // Путь к картинке
    }
} catch (PDOException $e) {
    // Если падает ошибка, просто оставим аватарку пустой
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет | EcoKazakhstan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
    <header class="main-header">
        <div class="container header-flex">
            <a href="index.html" class="logo">Eco<span>Kazakhstan</span></a>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.html">Главная</a></li>
                    <li><a href="problems.html">Проблемы</a></li>
                    <li><a href="nature.html">Заповедники</a></li>
                    <li><a href="green-tech.html">Эко-инициативы</a></li>
                    <li><a href="blog.html">Новости</a></li>
                    <li><a href="profile.php" class="active">Кабинет</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero-section">
            <div class="container">
                <h1>Ваш эко-профиль</h1>
                <p>Управляйте своими данными, обновляйте аватар волонтера и оставайтесь на связи с сообществом.</p>
            </div>
        </section>

        <section class="profile-section-wrapper">
            <div class="container">
                
                <div class="eco-profile-card">
                    <h2 class="profile-card-title">Личный кабинет</h2>
                    <p class="profile-welcome">Добро пожаловать, <span class="profile-username"><?php echo htmlentities($user_name); ?></span>!</p>
                    
                    <div class="profile-avatar-container">
                        <div class="profile-avatar-circle" style="width: 120px; height: 120px; border-radius: 50%; background: #e8f5e9; border: 3px solid #2ecc71; display: flex; justify-content: center; align-items: center; overflow: hidden; margin: 0 auto 20px auto;">
                            <?php if (!empty($avatar_path) && file_exists($avatar_path)): ?>
                                <img src="<?php echo htmlspecialchars($avatar_path); ?>?v=<?php echo time(); ?>" alt="Аватар" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 70px; height: 70px;">
                                    <path d="M12 12C14.21 12 16 10.21 16 8C16 5.79 14.21 4 12 4C9.79 4 8 5.79 8 8C8 10.21 9.79 12 12 12ZM12 14C9.33 14 4 15.34 4 18V20H20V18C20 15.34 14.67 14 12 14Z" fill="#1b3a24"/>
                                </svg>
                            <?php endif; ?>
                        </div>
                    </div>

                    <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="profile-form">
                        
                        <div class="profile-group">
                            <label class="profile-label">Сменить фото профиля:</label>
                            <input type="file" name="avatar" class="profile-file-input" accept="image/*">
                        </div>

                        <div class="profile-group">
                            <label class="profile-label">Имя пользователя</label>
                            <input type="text" name="username" class="profile-input" value="<?php echo htmlentities($user_name); ?>" required>
                        </div>

                        <div class="profile-group">
                            <label class="profile-label">Новый пароль</label>
                            <input type="password" name="new_password" class="profile-input" placeholder="Введите новый пароль">
                        </div>

                        <button type="submit" class="profile-btn-submit">Сохранить всё</button>
                    </form>

                    <div style="margin-top: 15px;">
                        <a href="logout.php" class="profile-btn-logout" style="display: block; text-decoration: none; text-align: center; background-color: #e74c3c; color: #ffffff; padding: 12px; border-radius: 6px; font-weight: bold; font-size: 1rem; transition: background 0.3s;">
                            Выйти из аккаунта
                        </a>
                    </div>

                </div>

            </div>
        </section>
    </main>

    <footer class="main-footer">
        <div class="container footer-flex">
            <p>&copy; 2026 EcoKazakhstan. Экологический проект.</p>
            <p>Республика Казахстан</p>
        </div>
    </footer>
</body>
</html>