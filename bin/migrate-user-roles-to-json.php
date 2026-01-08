#!/usr/bin/env php
<?php
/**
 * Script de migration pour convertir les rôles des utilisateurs
 * du format PHP serialize vers JSON (Doctrine ORM 3.x)
 *
 * Usage: php bin/migrate-user-roles-to-json.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Charger les variables d'environnement
(new Dotenv())->bootEnv(__DIR__ . '/../.env');

// Configuration de la base de données depuis les variables d'environnement
$databaseUrl = $_ENV['DATABASE_URL'] ?? '';
if (empty($databaseUrl)) {
    echo "Erreur: DATABASE_URL n'est pas défini dans les variables d'environnement.\n";
    exit(1);
}

// Parser l'URL de la base de données
preg_match('/^mysql:\/\/([^:]+):([^@]+)@([^:]+):(\d+)\/([^?]+)/', $databaseUrl, $matches);
if (count($matches) < 6) {
    echo "Erreur: Format de DATABASE_URL invalide.\n";
    exit(1);
}

[, $username, $password, $host, $port, $dbname] = $matches;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connexion à la base de données '$dbname' réussie.\n";

    // Récupérer tous les utilisateurs
    $stmt = $pdo->query("SELECT id, username, roles FROM pnu_user");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Nombre d'utilisateurs trouvés: " . count($users) . "\n\n";

    $updated = 0;
    $errors = 0;
    $skipped = 0;

    foreach ($users as $user) {
        $id = $user['id'];
        $username = $user['username'];
        $serializedRoles = $user['roles'];

        // Si c'est déjà du JSON, on passe
        if (is_string($serializedRoles) && (str_starts_with(trim($serializedRoles), '[') || str_starts_with(trim($serializedRoles), '{'))) {
            $skipped++;
            continue;
        }

        // Désérialiser le format PHP
        $rolesArray = @unserialize($serializedRoles);

        if ($rolesArray === false && $serializedRoles !== 'b:0;') {
            echo "User ID $id ($username): Erreur de désérialisation - '$serializedRoles'\n";
            $errors++;
            continue;
        }

        // Si la désérialisation a échoué mais que c'est une valeur vide, utiliser un tableau vide
        if ($rolesArray === false) {
            $rolesArray = [];
        }

        // Convertir en JSON
        $jsonRoles = json_encode($rolesArray);

        echo "User ID $id ($username): Converti\n";

        // Mettre à jour en base
        $updateStmt = $pdo->prepare("UPDATE pnu_user SET roles = :roles WHERE id = :id");
        $updateStmt->execute([
            'roles' => $jsonRoles,
            'id' => $id
        ]);

        $updated++;
    }

    echo "\n======================\n";
    echo "Migration terminée!\n";
    echo "Utilisateurs mis à jour: $updated\n";
    echo "Utilisateurs déjà en JSON (ignorés): $skipped\n";
    echo "Erreurs: $errors\n";
    echo "======================\n";

    exit($errors > 0 ? 1 : 0);

} catch (PDOException $e) {
    echo "Erreur de connexion: " . $e->getMessage() . "\n";
    exit(1);
}
