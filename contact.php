<?php
require_once "config/database.php";
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Контакти - SmartLock</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-content">
                <a href="index.php" class="logo">SmartLock</a>
                <div class="nav-links">
                    <a href="index.php" class="nav-link">Головна</a>
                    <a href="contact.php" class="nav-link active">Контакти</a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <section class="contact-hero">
            <div class="container">
                <div class="contact-header">
                    <h1 class="page-title">Зв'яжіться з нами</h1>
                    <p class="page-description">
                        Наші експерти готові відповісти на всі ваші запитання та 
                        запропонувати найкраще рішення для вашого бізнесу
                    </p>
                </div>
            </div>
        </section>

        <section class="contact-section">
            <div class="container">
                <div class="contact-content">

                    <div class="contact-info">
                        <h2>Наші контакти</h2>
                        <div class="info-items">
                            <div class="info-item">
                                <div class="info-icon">
                                    <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                </div>
                                <div class="info-text">
                                    <h3>Адреса офісу</h3>
                                    <p>вул. Степана Бандери, 30<br>5 корпус Львівська Політехніка</p>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                </div>
                                <div class="info-text">
                                    <h3>Телефон</h3>
                                    <p>+380 66 40 52 588<br>Пн-Пт: 9:00 - 18:00</p>
                                </div>
                            </div>

                            <div class="info-item">
                                <div class="info-icon">
                                    <svg viewBox="0 0 24 24"><path d="M4 4h16v16H4z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                </div>
                                <div class="info-text">
                                    <h3>Email</h3>
                                    <p>hordiisvd@gmail.com</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="contact-form-wrapper">
                        <form class="contact-form" id="contactForm">
                            <h2>Надішліть запит</h2>
                            <p class="form-description">Заповніть форму і ми зв'яжемося з вами протягом 24 годин</p>

                            <div class="form-group">
                                <label>Назва компанії *</label>
                                <input type="text" name="company" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Ім'я *</label>
                                    <input type="text" name="name" required>
                                </div>
                                <div class="form-group">
                                    <label>Посада</label>
                                    <input type="text" name="position">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label>Email *</label>
                                    <input type="email" name="email" required>
                                </div>
                                <div class="form-group">
                                    <label>Телефон *</label>
                                    <input type="tel" name="phone" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Кількість замків</label>
                                <select name="quantity">
                                    <option value="">Оберіть</option>
                                    <option value="1-10">1-10 шт</option>
                                    <option value="11-50">11-50 шт</option>
                                    <option value="51-100">51-100 шт</option>
                                    <option value="100+">100+</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Повідомлення</label>
                                <textarea name="message" rows="5"></textarea>
                            </div>

                            <button type="submit" class="btn-primary btn-large">Відправити запит</button>

                            <div id="formStatus" style="margin-top:15px;"></div>
                        </form>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <script>
        document.getElementById("contactForm").addEventListener("submit", function(e){
            e.preventDefault();

            let formData = new FormData(this);

            fetch("submit-form.php", {
                method: "POST",
                body: formData
            })
            .then(r => r.text())
            .then(res => {
                document.getElementById("formStatus").innerHTML = 
                    "<span style='color:green'>Ваш запит успішно надіслано!</span>";
                document.getElementById("contactForm").reset();
            })
            .catch(() => {
                document.getElementById("formStatus").innerHTML =
                    "<span style='color:red'>Помилка відправки!</span>";
            });
        });
    </script>

</body>
</html>
