USE orbitpesa;
UPDATE users SET password = '$2y$12$Tx3eqks2iiSH/H8NpKtVke87vvsgZEtwh2deLVSIktI8wR8RYY1Tu' WHERE email = 'demo@orbitpesa.com';
UPDATE admins SET password = '$2y$12$Tx3eqks2iiSH/H8NpKtVke87vvsgZEtwh2deLVSIktI8wR8RYY1Tu' WHERE email = 'admin@orbitpesa.com';
