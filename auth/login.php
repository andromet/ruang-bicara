<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Login - Ruang Bicara</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-page">

    <div class="login-card">
        <div class="login-logo">
            <h2 class="login-title">Ruang Bicara</h2>
            <p class="login-subtitle">Silakan login untuk melanjutkan</p>
        </div>

        <form action="proses_login.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="nis_nip">NIS / NIP</label>
                <input type="text" id="nis_nip" name="nis_nip" placeholder="Masukkan NIS / NIP" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan Password" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn w-full">Login</button>
        </form>

        <div class="login-footer">
            <p>Hubungi guru BK jika mengalami kendala login</p>
        </div>
    </div>

</body>
</html>