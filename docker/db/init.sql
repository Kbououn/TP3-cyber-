-- Script d'initialisation "à la main" : pas de migrations Doctrine, pas de
-- fixtures versionnées proprement -> mauvaise pratique volontaire (Cours OWASP A06 / bonnes pratiques).

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    roles JSON NOT NULL,
    security_answer VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    label VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    content TEXT,
    CONSTRAINT fk_invoice_owner FOREIGN KEY (owner_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author VARCHAR(180) NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Comptes de démonstration. Mots de passe en clair : admin / alice123 / bob123
-- (stockés en MD5, sans sel -> voir Cours OWASP A02).
INSERT INTO users (email, password, roles, security_answer) VALUES
    ('admin@poc-owasp.local', MD5('admin'), '["ROLE_ADMIN"]', 'Rex'),
    ('alice@poc-owasp.local', MD5('alice123'), '["ROLE_USER"]', 'Bella'),
    ('bob@poc-owasp.local', MD5('bob123'), '["ROLE_USER"]', 'Milo');

INSERT INTO invoices (owner_id, label, amount, content) VALUES
    (1, 'Facture admin - Licence annuelle', 4200.00, 'Confidentiel : renouvellement du contrat administrateur.'),
    (2, 'Facture Alice - Prestation de conseil', 850.00, 'Mission de conseil réalisée pour Alice.'),
    (3, 'Facture Bob - Contrat de maintenance', 320.00, 'Maintenance trimestrielle pour Bob.');

INSERT INTO comments (author, content) VALUES
    ('alice', 'Super article, merci pour le partage !'),
    ('bob', 'Je ne suis pas convaincu par cette approche.');
