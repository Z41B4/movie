<?php


ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);


require_once("globals.php");
require_once("db.php");
require_once("models/Movie.php");
require_once("models/Message.php");
require_once("dao/UserDAO.php");
require_once("dao/MovieDAO.php");

$message = new Message($BASE_URL);
$userDao = new UserDAO($conn, $BASE_URL);
$movieDao = new MovieDAO($conn, $BASE_URL);

// Resgata o tipo do formulário
$type = filter_input(INPUT_POST, "type");

// Resgata dados do usuário
$userData = $userDao->verifyToken();

if($type === "create") {

  // Receber os dados dos inputs
  $title = filter_input(INPUT_POST, "title");
  $description = filter_input(INPUT_POST, "description");
  $trailer = filter_input(INPUT_POST, "trailer");
  $category = filter_input(INPUT_POST, "category");
  $length = filter_input(INPUT_POST, "length");

  $movie = new Movie();

  // Validação mínima de dados
  if(!empty($title) && !empty($description) && !empty($category)) {

    $movie->title = $title;
    $movie->description = $description;
    $movie->trailer = $trailer;
    $movie->category = $category;
    $movie->length = $length;
    $movie->users_id = $userData->id;

    // Upload de imagem do filme
    if(isset($_FILES["image"]) && !empty($_FILES["image"]["tmp_name"])) {

      $image = $_FILES["image"];
      $imageTypes = ["image/jpeg", "image/jpg", "image/png"];
      $jpgArray = ["image/jpeg", "image/jpg"];

      // Checando tipo da imagem
      if(in_array($image["type"], $imageTypes)) {

        // Gerando o nome da imagem
        $imageName = $movie->imageGenerateName();

        // Processar e salvar imagem conforme tipo
        if(in_array($image["type"], $jpgArray)) {
          $imageFile = imagecreatefromjpeg($image["tmp_name"]);
          imagejpeg($imageFile, "./img/movies/" . $imageName, 100);
        } else {
          $imageFile = imagecreatefrompng($image["tmp_name"]);
          imagepng($imageFile, "./img/movies/" . $imageName);
        }

        $movie->image = $imageName;

      } else {
        $message->setMessage("Tipo inválido de imagem, insira png ou jpg!", "error", "back");
        exit;
      }
    }

    $movieDao->create($movie);

  } else {
    $message->setMessage("Você precisa adicionar pelo menos: título, descrição e categoria!", "error", "back");
  }

} else if($type === "delete") {

  $id = filter_input(INPUT_POST, "id");

  $movie = $movieDao->findById($id);

  if($movie) {
    if($movie->users_id === $userData->id) {
      $movieDao->destroy($movie->id);
    } else {
      $message->setMessage("Informações inválidas!", "error", "index.php");
    }
  } else {
    $message->setMessage("Informações inválidas!", "error", "index.php");
  }

} else if($type === "update") {

  $title = filter_input(INPUT_POST, "title");
  $description = filter_input(INPUT_POST, "description");
  $trailer = filter_input(INPUT_POST, "trailer");
  $category = filter_input(INPUT_POST, "category");
  $length = filter_input(INPUT_POST, "length");
  $id = filter_input(INPUT_POST, "id");

  $movieData = $movieDao->findById($id);

  if($movieData) {

    if($movieData->users_id === $userData->id) {

      if(!empty($title) && !empty($description) && !empty($category)) {

        $movieData->title = $title;
        $movieData->description = $description;
        $movieData->trailer = $trailer;
        $movieData->category = $category;
        $movieData->length = $length;

        // Upload da nova imagem
        if(isset($_FILES["image"]) && !empty($_FILES["image"]["tmp_name"])) {

          $image = $_FILES["image"];
          $imageTypes = ["image/jpeg", "image/jpg", "image/png"];
          $jpgArray = ["image/jpeg", "image/jpg"];

          if(in_array($image["type"], $imageTypes)) {

            $movie = new Movie();
            $imageName = $movie->imageGenerateName();

            if(in_array($image["type"], $jpgArray)) {
              $imageFile = imagecreatefromjpeg($image["tmp_name"]);
              imagejpeg($imageFile, "./img/movies/" . $imageName, 100);
            } else {
              $imageFile = imagecreatefrompng($image["tmp_name"]);
              imagepng($imageFile, "./img/movies/" . $imageName);
            }

            $movieData->image = $imageName;

          } else {
            $message->setMessage("Tipo inválido de imagem, insira png ou jpg!", "error", "back");
            exit;
          }
        }

        $movieDao->update($movieData);

      } else {
        $message->setMessage("Você precisa adicionar pelo menos: título, descrição e categoria!", "error", "back");
      }

    } else {
      $message->setMessage("Informações inválidas!", "error", "index.php");
    }

  } else {
    $message->setMessage("Informações inválidas!", "error", "index.php");
  }

} else {
  $message->setMessage("Informações inválidas!", "error", "index.php");
}
