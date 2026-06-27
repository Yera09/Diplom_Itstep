<?php
// Стартуем сессию, чтобы сайт "запомнил", что пользователь вошел
session_start();

try {
    // Подключаемся к нашей базе SQLite
    $db = new PDO('sqlite:database.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}

// Проверяем, что форма отправлена через POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        die("Пожалуйста, заполните все поля.");
    }

    // Ищем пользователя в базе по его Email
    $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Если пользователь найден
    if ($user) {
        // Проверяем, совпадает ли введенный пароль с хэшем в базе данных
        if (password_verify($password, $user['password'])) {
            
            // Запоминаем пользователя в сессии
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];

            // Успешный вход — перенаправляем на главную страницу
            echo "<script>
                    alert('Добро пожаловать, " . $user['full_name'] . "!');
                    window.location.href = 'index.html';
                  </script>";
            exit;
        } else {
            // Если пароль не подошел
            echo "<script>
                    alert('Неверный пароль!');
                    window.location.href = 'login.html';
                  </script>";
        }
    } else {
        // Если пользователя с такой почтой нет в базе
        echo "<script>
                alert('Пользователь с таким Email не найден!');
                window.location.href = 'login.html';
              </script>";
    }
} else {
    header('Location: login.html');
    exit;
}
?>