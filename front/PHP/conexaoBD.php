<?php
$host = "localhost";
$dbname = "tcc_alimento";
$username = "root";
$password = "";

try{
    $pdo = new PDO ("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("SET NAMES utf8");
    $pdo->exec("SET CHARACTER SET utf8");
    $connection_status = $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);

} catch (PDOException $e){
    die("Erro na conexão ao Banco de Dados: " . $e->getMessage());
}

?>