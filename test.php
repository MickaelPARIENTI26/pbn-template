<?php
echo "PHP fonctionne!<br>";

echo "Variables d'environnement:<br>";
echo "DB_HOST: " . (getenv('DB_HOST') ?: 'NON DEFINI') . "<br>";
echo "DB_NAME: " . (getenv('DB_NAME') ?: 'NON DEFINI') . "<br>";
echo "PORT: " . (getenv('PORT') ?: 'NON DEFINI') . "<br>";

echo "<br>Test connexion DB:<br>";
try {
    $host = getenv('DB_HOST') ?: '{{DB_HOST}}';
    $name = getenv('DB_NAME') ?: '{{DB_NAME}}';
    $user = getenv('DB_USER') ?: '{{DB_USER}}';
    $pass = getenv('DB_PASS') ?: '{{DB_PASS}}';

    echo "Connexion à: $host / $name<br>";

    $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    echo "CONNEXION OK!";
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage();
}
