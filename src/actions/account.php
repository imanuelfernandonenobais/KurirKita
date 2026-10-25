<?php

/** Perubahan profil dan password oleh pengguna login. */

function update_profile(): never
{
    $id = (int) $_SESSION['user_id'];
    $name = form_string('name', 120);
    $email = strtolower(form_string('email', 190));

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new DomainException('Nama atau alamat email tidak valid.');
    }

    if (one('SELECT id FROM users WHERE email=? AND id<>?', [$email, $id])) {
        throw new DomainException('Email sudah digunakan oleh akun lain.');
    }

    db()->prepare('UPDATE users SET name=?, email=? WHERE id=?')->execute([$name, $email, $id]);
    flash('success', 'Profil berhasil diperbarui.');
    go('profile');
}

function change_password(): never
{
    $id = (int) $_SESSION['user_id'];
    $old = (string) ($_POST['old_password'] ?? '');
    $new = (string) ($_POST['new_password'] ?? '');
    $confirmation = (string) ($_POST['confirm_password'] ?? '');
    $user = one('SELECT password_hash FROM users WHERE id=?', [$id]);

    if (!$user || !password_verify($old, $user['password_hash'])) {
        throw new DomainException('Password lama salah.');
    }

    if (strlen($new) < 8) {
        throw new DomainException('Password baru minimal 8 karakter.');
    }

    if ($new !== $confirmation) {
        throw new DomainException('Konfirmasi password tidak cocok.');
    }

    db()->prepare('UPDATE users SET password_hash=? WHERE id=?')
        ->execute([password_hash($new, PASSWORD_DEFAULT), $id]);

    flash('success', 'Password berhasil diganti.');
    go('profile');
}
