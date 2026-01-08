#!/usr/bin/env php
<?php
/**
 * Script de migration pour convertir les rôles des groupes
 * du format PHP serialize vers JSON (Doctrine ORM 3.x)
 *
 * Usage: php bin/migrate-group-roles-to-json.php
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

    // Récupérer tous les groupes
    $stmt = $pdo->query("SELECT id, name, roles FROM pnu_group");
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Nombre de groupes trouvés: " . count($groups) . "\n\n";

    $updated = 0;
    $errors = 0;
    $skipped = 0;

    foreach ($groups as $group) {
        $id = $group['id'];
        $name = $group['name'];
        $serializedRoles = $group['roles'];

        // Si c'est déjà du JSON, on passe
        if (is_string($serializedRoles) && (str_starts_with(trim($serializedRoles), '[') || str_starts_with(trim($serializedRoles), '{'))) {
            echo "Group ID $id ($name): Déjà au format JSON, ignoré.\n";
            $skipped++;
            continue;
        }

        // Désérialiser le format PHP
        $rolesArray = @unserialize($serializedRoles);

        if ($rolesArray === false && $serializedRoles !== 'b:0;') {
            echo "Group ID $id ($name): Erreur de désérialisation - '$serializedRoles'\n";
            $errors++;
            continue;
        }

        // Si la désérialisation a échoué mais que c'est une valeur vide, utiliser un tableau vide
        if ($rolesArray === false) {
            $rolesArray = [];
        }

        // Convertir en JSON
        $jsonRoles = json_encode($rolesArray);

        echo "Group ID $id ($name): Converti\n";

        // Mettre à jour en base
        $updateStmt = $pdo->prepare("UPDATE pnu_group SET roles = :roles WHERE id = :id");
        $updateStmt->execute([
            'roles' => $jsonRoles,
            'id' => $id
        ]);

        $updated++;
    }

    echo "\n======================\n";
    echo "Migration terminée!\n";
    echo "Groupes mis à jour: $updated\n";
    echo "Groupes déjà en JSON (ignorés): $skipped\n";
    echo "Erreurs: $errors\n";
    echo "======================\n";

    exit($errors > 0 ? 1 : 0);

} catch (PDOException $e) {
    echo "Erreur de connexion: " . $e->getMessage() . "\n";
    exit(1);
}
