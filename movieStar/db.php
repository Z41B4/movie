<?php


  $host= "localhost";
  $db = "movieStar";
  $user = "zaiba";
  $pass = "sotapa157";


  $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);

  // Habilitar erros PDO
  
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);