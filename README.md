# poc-symfony-owasp-cve

Application Symfony **volontairement vulnérable**, construite à des fins pédagogiques (BTS SIO,
module DevSecOps). Elle sert de support au TP *"PoC Symfony : cartographier l'OWASP Top 10 et
scanner les CVE avec Trivy"*.

## ⚠️ Ne jamais déployer ce projet sur un serveur exposé à Internet

Ce dépôt contient intentionnellement : des dépendances obsolètes avec de vraies CVE connues, une
image Docker basée sur un PHP ancien et non maintenu, des identifiants et secrets codés en dur, et
des failles applicatives couvrant les dix catégories de l'[OWASP Top 10 (2021)](https://owasp.org/Top10/).
Utilisation strictement locale.

## Démarrage rapide

**Tout en Docker :**

```bash
docker compose -f docker-compose.yml up --build -d
```

Application : http://localhost:8080 — Adminer : http://localhost:8081

**Code en local, base de données en Docker :**

```bash
composer install --ignore-platform-req=php
docker compose up -d
symfony serve   # ou : php -S 127.0.0.1:8000 -t public
```

Comptes de démonstration : `admin@poc-owasp.local` / `admin`, `alice@poc-owasp.local` / `alice123`.

## Documentation pédagogique complète

Voir le cours Obsidian *"Cours Trivy - Analyse de CVE"*, fichier
`TP/TP 3 - PoC Symfony OWASP - Scanner et documenter une application vulnérable.md`, pour :
la présentation technique détaillée, les instructions de scan Trivy, la démarche d'exploration
OWASP Top 10, et la méthode de catégorisation CVE/CVSS attendue.
